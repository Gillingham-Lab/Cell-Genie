from typing import Optional, TypeAlias, Literal
import json

import argh
from argh import arg
from argh.decorators import named
import lmfit
import numpy as np

MODELS = {}

def model(model_class):
    if "__modelName__" in model_class.__dict__:
        name = model_class.__modelName__
    else:
        name = model_class.__name__

    MODELS[name] = model_class
    return model_class


class AbstractModel:
    @classmethod
    def get_model(cls):
        return lmfit.Model(cls.model)

    @staticmethod
    def get_defaults():
        return {
            "evaluation": {
                "spacing": "linear",
            }
        }

@model
class ScaledModel(AbstractModel):
    label = "Linearly Scaled Model"
    formula = "x·scaling + offset"
    param_help = {
            "x": {
                "label": "x",
                "help": "The variable you change"
            },
            "y": {
                "label": "Signal y",
                "help": "The measured signal",
            },
            "scaling": {
                "label": "Scaling factor",
                "help": "The scaling of the signal",
            },
            "offset": {
                "label": "Offset",
                "help": "The offset from the y-axis"
            },
        }

    @staticmethod
    def model(x, scaling=1, offset=0):
        return x*scaling + offset


@model
class BindingModel(AbstractModel):
    label = "KD binding model"
    formula = "f([(x+c+KD) - ([x+c+KD]^2 - 4·x·c)^0.5] / (2·c))·scaling + offset"
    param_help = {
        "x": {
            "label": "Variable concentration x",
            "help": "The initial concentration of the titrated compound"
        },
        "y": {
            "label": "Signal y",
            "help": "The measured signal",
        },
        "c": {
            "label": "Concentration c",
            "help": "The initial concentration of the non-titrated compound",
        },
        "KD": {
            "label": "Dissociation constant KD",
            "help": "The dissociation constant measuring the stability of the complex.",
        },
        "scaling": {
            "label": "Scaling factor",
            "help": "The scaling of the signal",
        },
        "offset": {
            "label": "Offset",
            "help": "The offset from the y-axis"
        },
    }

    @staticmethod
    def model(x, KD=1, c=1, scaling=1, offset=0):
        f = ((x + c + KD) - ((x + c + KD)**2 - 4 * x * c)**(0.5) )/(2*c)
        y = f*scaling + offset
        return y

    @classmethod
    def get_model(cls):
        return lmfit.Model(cls.model)

    @staticmethod
    def get_defaults():
        return {
            **AbstractModel.get_defaults(),
            **{
                "params": {
                    "KD": {
                        "min": 0,
                        "vary": True,
                    },
                    "c": {
                        "min": 0,
                        "vary": False,
                    },
                    "offset": {
                        "vary": True,
                    },
                    "scaling": {
                        "vary": True,
                    }
                },
                "evaluation": {
                    "spacing": "log",
                }
            }
        }

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
    ci = fit.conf_interval(sigmas=[ci_value])

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
            "x": x_fit.tolist(),
            "y": y_fit.tolist(),
            "yerr": y_unc.tolist(),
        },
    }

    for param in fit.params:
        reply["params"][param] = {
            "value": fit.params[param].value,
            "stderr": fit.params[param].stderr,
            "ci": (ci[param][0][1], ci[param][-1][1]) if configuration["params"][param]["vary"] is True else None,
            "vary": configuration["params"][param]["vary"],
        }

    print(json.dumps(reply))



argh.dispatch_commands([command_list, command_fit], old_name_mapping_policy=False)
