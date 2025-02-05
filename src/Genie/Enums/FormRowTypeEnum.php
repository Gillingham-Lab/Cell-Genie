<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum FormRowTypeEnum: string
{
    case TextType = "text";
    case TextAreaType = "textarea";
    case IntegerType = "integer";
    case FloatType = "float";
    case EntityType = "entity";
    case DateType = "date";
    case ImageType = "image";
    case ExpressionType = "expression";
    case ModelParameterType = "modelParameter";

    public function getLabel(): string
    {
        return match($this) {
            self::TextType => "String",
            self::TextAreaType => "Text",
            self::IntegerType => "Integer",
            self::FloatType => "Float",
            self::EntityType => "Database entity",
            self::DateType => "Date",
            self::ImageType => "Image",
            self::ExpressionType => "Expression",
            self::ModelParameterType => "Model parameter",
        };
    }
}
