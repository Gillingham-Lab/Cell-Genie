import lmfit
from bin.models.AbstractModel import model, AbstractModel

@model
class ThreeParameterLogistics(AbstractModel):
    label = "3-Parameter logistic curve"
    formula = "y = maximum + (minimum - maximum) / (1 + (x/IC50))"
    param_help = {
        "x": {
            "label": "Variable concentration x",
            "help": "The initial concentration of the titrated compound"
        },
        "y": {
            "label": "Signal y",
            "help": "The measured signal",
        },
        "IC50": {
            "label": "IC50",
            "help": "The infliction point of the curve. In units of x.",
        },
        "minimum": {
            "label": "Minimum signal",
            "help": "The lower boundary of the signal. In units of y.",
        },
        "maximum": {
            "label": "Maximum signal",
            "help": "The upper boundary of the signal. In units of y."
        },
    }

    @staticmethod
    def model(x, IC50=1, minimum=0, maximum=1):
        signal = maximum + (minimum - maximum) / (1 + (x/IC50))
        return signal

    @classmethod
    def get_model(cls):
        return lmfit.Model(cls.model)

    @staticmethod
    def get_defaults():
        return {
            **AbstractModel.get_defaults(),
            **{
                "params": {
                    "IC50": {
                        "min": 0,
                        "vary": True,
                    },
                    "minimum": {
                        "vary": True,
                    },
                    "maximum": {
                        "vary": True,
                    }
                },
                "evaluation": {
                    "spacing": "log",
                }
            }
        }

@model
class FourParameterLogistics(AbstractModel):
    label = "4-Parameter logistic curve"
    formula = "y = maximum + (minimum - maximum) / (1 + (x/IC50)^hillSlope)"
    param_help = {
        "x": {
            "label": "Variable concentration x",
            "help": "The initial concentration of the titrated compound"
        },
        "y": {
            "label": "Signal y",
            "help": "The measured signal",
        },
        "IC50": {
            "label": "IC50",
            "help": "The infliction point of the curve. In units of x.",
        },
        "minimum": {
            "label": "Minimum signal",
            "help": "The lower boundary of the signal. In units of y.",
        },
        "maximum": {
            "label": "Maximum signal",
            "help": "The upper boundary of the signal. In units of y."
        },
        "hillSlope": {
            "label": "Hill slope",
            "help": "The steepness of the infliction. Unitless."
        }
    }

    @staticmethod
    def model(x, IC50=1, minimum=0, maximum=1, hillSlope=1):
        signal = maximum + (minimum - maximum) / (1 + (x/IC50)**hillSlope)
        return signal

    @classmethod
    def get_model(cls):
        return lmfit.Model(cls.model)

    @staticmethod
    def get_defaults():
        return {
            **AbstractModel.get_defaults(),
            **{
                "params": {
                    "IC50": {
                        "min": 0,
                        "vary": True,
                    },
                    "minimum": {
                        "vary": True,
                    },
                    "maximum": {
                        "vary": True,
                    },
                    "hillSlope": {
                        "vary": True,
                        "min": 0,
                    }
                },
                "evaluation": {
                    "spacing": "log",
                }
            }
        }

@model
class FiveParameterLogistics(AbstractModel):
    label = "5-Parameter logistic curve"
    formula = "y = maximum + (minimum - maximum) / (1 + (x/IC50)^hillSlope)"
    param_help = {
        "x": {
            "label": "Variable concentration x",
            "help": "The initial concentration of the titrated compound"
        },
        "y": {
            "label": "Signal y",
            "help": "The measured signal",
        },
        "IC50": {
            "label": "IC50",
            "help": "The infliction point of the curve. In units of x.",
        },
        "minimum": {
            "label": "Minimum signal",
            "help": "The lower boundary of the signal. In units of y.",
        },
        "maximum": {
            "label": "Maximum signal",
            "help": "The upper boundary of the signal. In units of y."
        },
        "hillSlope": {
            "label": "Hill slope",
            "help": "The steepness of the infliction. Unitless."
        },
        "asymmetry": {
            "label": "Asymmetry factor",
            "help": "The asymmetry of the curve. Unitless."
        },
    }

    @staticmethod
    def model(x, IC50=1, minimum=0, maximum=1, hillSlope=1, asymmetry=1):
        signal = maximum + (minimum - maximum) / (1 + ((2**(1/asymmetry)-1) + (x/IC50)**hillSlope)**asymmetry)
        return signal

    @classmethod
    def get_model(cls):
        return lmfit.Model(cls.model)

    @staticmethod
    def get_defaults():
        return {
            **AbstractModel.get_defaults(),
            **{
                "params": {
                    "IC50": {
                        "min": 0,
                        "vary": True,
                    },
                    "minimum": {
                        "vary": True,
                    },
                    "maximum": {
                        "vary": True,
                    },
                    "hillSlope": {
                        "vary": True,
                        "min": 0,
                    },
                    "asymmetry": {
                        "vary": True,
                        "min": 0,
                    }
                },
                "evaluation": {
                    "spacing": "log",
                }
            }
        }

