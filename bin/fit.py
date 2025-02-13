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

@model
class CompetitiveBindingModel(AbstractModel):
    label = "Competitive binding model"
    formula = "Z.-X. Wang, FEBS Letters 360 (1995), 111-114"
    param_help = {
        "x": {
            "label": "Variable concentration x",
            "help": "The initial concentration of the titrated compound."
        },
        "y": {
            "label": "Signal y",
            "help": "The measured signal.",
        },
        "c_p": {
            "label": "Probe concentration c_p",
            "help": "The initial concentration of the probe.",
        },
        "c_c": {
            "label": "Competitor concentration c_c",
            "help": "The initial concentration of the competitor."
        },
        "KD_p": {
            "label": "Probe's dissociation constant KD_p",
            "help": "The probe's dissociation constant measuring the stability of the complex. "
                "Should be known from previous experiments.",
        },
        "KD_c": {
            "label": "Competitor's dissociation constant KD_c",
            "help": "The competitor's dissociation constant measuring its complex."
                "Usually what is tried to find with this experiment."
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
    def model(x, KD_p=1, c_p=1, KD_c=1, c_c=1, scaling=1, offset=0):
        A0 = c_p
        B0 = c_c
        P0 = x
        KA = KD_p
        KB = KD_c

        a = KA + KB + A0 + B0 - P0
        b = KB*(A0 - P0) + KA*(B0 - P0) + KA*KB
        c = - KA*KB*P0

        θ = np.arccos(
            (- 2*a**3 + 9*a*b - 27*c) / (2 * ((a**2 - 3*b)**3)**(1/2))
        )
        r = 2*(a**2 - 3*b)**(1/2) * np.cos(θ/3) - a
        q = lambda A, K: (A*r / (3 * K + r))

        f = q(A0, KA) / A0
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
                    "KD_c": {
                        "min": 0,
                        "vary": True,
                    },
                    "c_c": {
                        "min": 0,
                        "vary": False,
                    },
                    "KD_p": {
                        "min": 0,
                        "vary": False,
                    },
                    "c_p": {
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
