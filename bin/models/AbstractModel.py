import lmfit

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