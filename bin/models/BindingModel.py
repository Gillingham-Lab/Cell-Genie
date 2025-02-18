import lmfit
import numpy as np
from bin.models.AbstractModel import model, AbstractModel

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