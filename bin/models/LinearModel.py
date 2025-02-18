import lmfit
from bin.models.AbstractModel import model, AbstractModel

@model
class LinearModel(AbstractModel):
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