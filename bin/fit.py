from typing import Optional, TypeAlias, Literal
import json
import warnings
import argh
from argh import arg
from argh.decorators import named
import lmfit
import numpy as np
from lmfit.minimizer import MinimizerException
from uncertainties import unumpy as unp

from bin.models import MODELS

Model: TypeAlias = Literal[*MODELS.keys()]


@named("list")
def command_list():
    output = {}
    for model_name, model_class in MODELS.items():
        model = model_class.get_model()
        output[model_name] = {
            "name": model_class.label if "label" in model_class.__dict__ else model_name,
            "formula": model_class.formula if "formula" in model_class.__dict__ else None,
            "param_help": model_class.param_help if "param_help" in model_class.__dict__ else None,
            "param_names": model.param_names,
            "defaults": model_class.get_defaults(),
        }

    return json.dumps(output)


def array_replace_recursive(default, configuration):
    new_dict = dict(default)

    for key, value in configuration.items():
        if key in new_dict:
            if value is None or value == "":
                continue
            elif isinstance(value, dict):
                new_dict[key] = array_replace_recursive(default[key], value)
            else:
                new_dict[key] = value
        else:
            new_dict[key] = value

    return new_dict

@named("eval")
@arg("configuration", help="A JSON encoded configuration to set up the model parameters")
def command_eval(model: Model, configuration: str):
    try:
        model_name = model
        model_class = MODELS[model]
        model = model_class.get_model()
    except KeyError:
        raise argh.CommandError("Model is not known")

    try:
        configuration = json.loads(configuration)
    except json.decoder.JSONDecodeError:
        raise argh.CommandError("JSON configuration was incorrect")

    evaluation = configuration["evaluation"]

    if evaluation["spacing"] == "linear":
        x_eval = np.linspace(evaluation["min"], evaluation["max"], evaluation["N"])
    else:
        x_eval = np.logspace(np.log10(evaluation["min"]), np.log10(evaluation["max"]), evaluation["N"])

    y_eval = model_class.model(x_eval, **configuration["params"])

    reply = {
        "x": [float_to_string(x) for x in x_eval.tolist()],
        "y": [float_to_string(x) for x in y_eval.tolist()],
    }

    print(json.dumps(reply))


@named("fit")
@arg("configuration", help="A JSON encoded configuration to set up the model parameters")
def command_fit(model: Model, configuration: str):
    try:
        model_name = model
        model_class = MODELS[model]
        model = model_class.get_model()
    except KeyError:
        raise argh.CommandError("Model is not known")

    try:
        configuration = json.loads(configuration)
    except json.decoder.JSONDecodeError:
        raise argh.CommandError("JSON configuration was incorrect")

    # Merge defaults with configuration
    defaults = model_class.get_defaults()
    configuration = array_replace_recursive(defaults, configuration)

    if "x" not in configuration:
        raise argh.CommandError("You must provide x values in order to fit a model.")
    if "y" not in configuration:
        raise argh.CommandError("You must provide y values in order to fit a model.")

    x = configuration["x"]
    y = configuration["y"]

    if len(x) != len(y):
        raise argh.CommandError(f"Dimensions for x and y must be the same (x: {len(x)} given, y: {len(y)} given)")

    # Confidence interval settings
    if "ci" not in configuration:
        ci_value = 0.95
    else:
        ci_value = configuration["ci"]

    # For evaluation of the model
    evaluation = {
        "min": min(x),
        "max": max(x),
        "N": len(x)*10,
        "spacing": "linear",
    }
    if "evaluation" in configuration:
        for k in ["min", "max", "N"]:
            evaluation[k] = configuration["evaluation"][k] if k in configuration["evaluation"] else evaluation[k]

        if "spacing" in configuration["evaluation"]:
            if configuration["evaluation"]["spacing"] in ["linear", "log"]:
                evaluation["spacing"] = configuration["evaluation"]["spacing"]

        if evaluation["spacing"] == "log":
            if evaluation["min"] == 0:
                evaluation["min"] = min(x)

            if evaluation["max"] == 0:
                evaluation["max"] = max(x)

            if evaluation["min"] > evaluation["max"]:
                evaluation["min"], evaluation["max"] = evaluation["max"], evaluation["min"]


    # Create params based on configuration
    params = model.make_params()
    if "params" in configuration:
        for param_name in params:
            if param_name not in configuration["params"]:
                continue

            if "initial" in configuration["params"][param_name]:
                params[param_name].set(value=configuration["params"][param_name]["initial"])

            if "vary" in configuration["params"][param_name]:
                params[param_name].set(vary=True if configuration["params"][param_name]["vary"] is True else False)

            if "min" in configuration["params"][param_name]:
                params[param_name].set(min=configuration["params"][param_name]["min"])

            if "max" in configuration["params"][param_name]:
                params[param_name].set(max=configuration["params"][param_name]["max"])

    # Fit!
    fit = model.fit(y, params, x=x)

    # Evaluate some parameters
    try:
        ci = fit.conf_interval(sigmas=[ci_value])
    except MinimizerException as e:
        ci = None
        warnings.warn(f"ConfidenceIntervalWarning: Determination of the confidence intervals was not possible. Reason: {e}")

    if evaluation["spacing"] == "linear":
        x_fit = np.linspace(evaluation["min"], evaluation["max"], evaluation["N"])
    else:
        x_fit = np.logspace(np.log10(evaluation["min"]), np.log10(evaluation["max"]), evaluation["N"])

    y_fit = fit.eval(x=x_fit)
    y_unc = fit.eval_uncertainty(x=x_fit, sigma=ci_value)

    reply = {
        "model": model_name,
        "params": {},
        "ci": ci_value,
        "evaluation": evaluation,
        "x": x,
        "y": y,
        "fit": {
            "x": [float_to_string(x) for x in x_fit.tolist()],
            "y": [float_to_string(y) for y in y_fit.tolist()],
            "yerr": [float_to_string(y) for y in y_unc.tolist()],
        },
    }

    for param in fit.params:
        reply["params"][param] = {
            "value": float_to_string(fit.params[param].value),
            "stderr": float_to_string(fit.params[param].stderr),
            "ci": (float_to_string(ci[param][0][1]), float_to_string(ci[param][-1][1])) if configuration["params"][param]["vary"] is True and ci is not None else None,
            "vary": configuration["params"][param]["vary"],
        }

    print(json.dumps(reply))

def float_to_string(float):
    if float is None:
        return None

    if np.isnan(float):
        return "NAN"
    elif np.isinf(float):
        if float < 0:
            return "-Inf"
        else:
            return "Inf"
    else:
        return float

argh.dispatch_commands([command_list, command_fit, command_eval], old_name_mapping_policy=False)
