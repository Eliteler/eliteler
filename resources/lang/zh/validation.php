<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    "accepted" => "：必须接受属性。",
    "active_url" => "：属性不是有效的URL。",
    "after" => "：属性必须是以下日期：日期。",
    "after_or_equal" => "：属性必须是以后的日期：日期。",
    "alpha" => "：属性可能只包含字母。",
    "alpha_dash" => "：属性只能包含字母，数字，破折号和下划线。",
    "alpha_num" => "：属性可能只包含字母和数字。",
    "array" => "：属性必须是一个数组。",
    "before" => "：属性必须是日期：日期。",
    "before_or_equal" => "：属性必须是日期或等于：日期的日期。",
    "between" => [
        "numeric" => "：属性必须在：min和：max之间。",
        "file" => "：属性必须在以下：最小和最大kilobytes之间。",
        "string" => "：属性必须在：min和：max字符之间。",
        "array" => "：属性必须具有：min和：max项目之间。"
    ],
    "boolean" => "：属性字段必须为真或错误。",
    "confirmed" => "：属性确认不匹配。",
    "date" => "：属性不是有效的日期。",
    "date_equals" => "：属性必须是等于：日期的日期。",
    "date_format" => "：属性与格式不匹配：格式。",
    "different" => "：属性和：其他必须不同。",
    "digits" => "：属性必须是：数字数字。",
    "digits_between" => "：属性必须在：min和：max数字之间。",
    "dimensions" => "：属性具有无效的图像维度。",
    "distinct" => "：属性字段具有重复值。",
    "email" => "：属性必须是有效的电子邮件地址。",
    "ends_with" => "：属性必须以以下内容之一结束：：值。",
    "exists" => "选定：属性无效。",
    "file" => "：属性必须是文件。",
    "filled" => "：属性字段必须具有值。",
    "gt" => [
        "numeric" => "：属性必须大于：值。",
        "file" => "：属性必须大于：kilobytes。",
        "string" => "：属性必须大于：价值字符。",
        "array" => "：属性必须具有不止：价值项目。"
    ],
    "gte" => [
        "numeric" => "：属性必须大于或相等：值。",
        "file" => "：属性必须大于或相等：值千字体。",
        "string" => "：属性必须大于或相等：价值字符。",
        "array" => "：属性必须具有：价值项目或更多。"
    ],
    "image" => "：属性必须是图像。",
    "in" => "选定：属性无效。",
    "in_array" => "：属性字段不存在：其他。",
    "integer" => "：属性必须是整数。",
    "ip" => "：属性必须是有效的IP地址。",
    "ipv4" => "：属性必须是有效的IPv4地址。",
    "ipv6" => "：属性必须是有效的IPv6地址。",
    "json" => "：属性必须是有效的JSON字符串。",
    "lt" => [
        "numeric" => "：属性必须小于：值。",
        "file" => "：属性必须小于：kilobytes。",
        "string" => "：属性必须小于：值字符。",
        "array" => "：属性必须少于：价值项目。"
    ],
    "lte" => [
        "numeric" => "：属性必须小于或相等：值。",
        "file" => "：属性必须小于或相等：值千字体。",
        "string" => "：属性必须小于或相等：价值字符。",
        "array" => "：属性不得拥有超过：价值项目。"
    ],
    "max" => [
        "numeric" => "：属性可能不大于：最大。",
        "file" => "：属性可能不大于：max kilobytes。",
        "string" => "：属性可能不大于：最大字符。",
        "array" => "：属性可能不超过：最大项目。"
    ],
    "mimes" => "：属性必须是type ：： values的文件。",
    "mimetypes" => "：属性必须是type ：： values的文件。",
    "min" => [
        "numeric" => "：属性必须至少：最小。",
        "file" => "：属性至少必须是：最小千数。",
        "string" => "：属性至少必须：最小字符。",
        "array" => "：属性必须至少具有：最小项目。"
    ],
    "not_in" => "选定：属性无效。",
    "not_regex" => "：属性格式无效。",
    "numeric" => "：属性必须是一个数字。",
    "password" => "密码不正确。",
    "present" => "：必须存在属性字段。",
    "regex" => "：属性格式无效。",
    "required" => "需要：属性字段。",
    "required_if" => "当：其他IS：value时，需要：属性字段。",
    "required_unless" => "需要：属性字段，除非以下：其他INS：值。",
    "required_with" => "当存在：值时，需要：属性字段。",
    "required_with_all" => "当存在值时，需要：属性字段。",
    "required_without" => "当不存在值时，需要：属性字段。",
    "required_without_all" => "当不存在时，需要：属性字段。",
    "same" => "：属性和：其他必须匹配。",
    "size" => [
        "numeric" => "：属性必须为：大小。",
        "file" => "：属性必须是：尺寸千字节。",
        "string" => "：属性必须是：大小字符。",
        "array" => "：属性必须包含：大小项目。"
    ],
    "starts_with" => "：属性必须以以下内容之一开始：：值。",
    "string" => "：属性必须是字符串。",
    "timezone" => "：属性必须是一个有效区域。",
    "unique" => "：属性已经采用。",
    "uploaded" => "：属性无法上传。",
    "url" => "：属性格式无效。",
    "uuid" => "：属性必须是有效的UUID。",
    "custom" => [
        "attribute-name" => [
            "rule-name" => "定制"
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
