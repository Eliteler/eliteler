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

    "accepted" => "Atribuut tuleb aktsepteerida.",
    "active_url" => "Atribuut ei ole kehtiv URL.",
    "after" => "Atribuut peab olema kuupäev pärast: kuupäev.",
    "after_or_equal" => "Atribuut peab olema kuupäev pärast või sellega võrdne: kuupäev.",
    "alpha" => "Atribuut võib sisaldada ainult tähti.",
    "alpha_dash" => "Atribuut võib sisaldada ainult tähti, numbreid, kriipsusid ja alalõikeid.",
    "alpha_num" => "Atribuut võib sisaldada ainult tähti ja numbreid.",
    "array" => ": Atribuut peab olema massiiv.",
    "before" => "Atribuut peab olema kuupäev enne: kuupäev.",
    "before_or_equal" => "Atribuut peab olema kuupäev enne või võrdne: kuupäev.",
    "between" => [
        "numeric" => "Atribuut peab olema vahemikus: MIN ja: MAX.",
        "file" => "Atribuut peab olema vahemikus: MIN ja: Max Kilobytes.",
        "string" => "Atribuut peab olema: min ja: max tähemärgid.",
        "array" => "Atribuudil peab olema: min ja: max üksused."
    ],
    "boolean" => "Atribuudi väli peab olema tõene või vale.",
    "confirmed" => "Atribuudi kinnitus ei ühti.",
    "date" => "Atribuut ei ole kehtiv kuupäev.",
    "date_equals" => "Atribuut peab olema kuupäev võrdne: kuupäev.",
    "date_format" => "Atribuut ei vasta vormingule: vormingule.",
    "different" => "Atribuut ja: Muu peab olema erinev.",
    "digits" => "Atribuut peab olema: numbrid numbrid.",
    "digits_between" => "Atribuut peab olema: min ja: max numbrid.",
    "dimensions" => "Atribuudil on valed mõõtmed.",
    "distinct" => "Atribuudiväljal on duplikaadi väärtus.",
    "email" => "Atribuut peab olema kehtiv e -posti aadress.",
    "ends_with" => "Atribuut peab lõppema ühe järgmistest :: Väärtused.",
    "exists" => "Valitud: atribuut on kehtetu.",
    "file" => "Atribuut peab olema fail.",
    "filled" => "Atribuudiväljal peab olema väärtus.",
    "gt" => [
        "numeric" => "Atribuut peab olema suurem kui: väärtus.",
        "file" => "Atribuut peab olema suurem kui: Väärtus kilobüüdid.",
        "string" => "Atribuut peab olema suurem kui: Väärtusmärgid.",
        "array" => "Atribuudil peab olema rohkem kui: väärtusüksused."
    ],
    "gte" => [
        "numeric" => "Atribuut peab olema suurem või võrdne: väärtus.",
        "file" => "Atribuut peab olema suurem või võrdne: Väärtus kilobüüdid.",
        "string" => "Atribuut peab olema suurem kui või võrdne: väärtusmärgid.",
        "array" => "Atribuudil peab olema: väärtusüksused või rohkem."
    ],
    "image" => "Atribuut peab olema pilt.",
    "in" => "Valitud: atribuut on kehtetu.",
    "in_array" => "The: Atribuudi väli ei eksisteeri: Muu.",
    "integer" => "Atribuut peab olema täisarv.",
    "ip" => "Atribuut peab olema kehtiv IP -aadress.",
    "ipv4" => "Atribuut peab olema kehtiv IPv4 aadress.",
    "ipv6" => "Atribuut peab olema kehtiv IPv6 aadress.",
    "json" => "Atribuut peab olema kehtiv JSON -stringi.",
    "lt" => [
        "numeric" => "Atribuut peab olema väiksem kui: väärtus.",
        "file" => "Atribuut peab olema väiksem kui: Väärtus kilobüüdid.",
        "string" => "Atribuut peab olema väiksem kui: Väärtusmärgid.",
        "array" => "Atribuudil peab olema vähem kui: väärtusüksused."
    ],
    "lte" => [
        "numeric" => "Atribuut peab olema väiksem või võrdne: väärtus.",
        "file" => "Atribuut peab olema väiksem või võrdne: Väärtus kilobüüdid.",
        "string" => "Atribuut peab olema väiksem või võrdne: väärtusmärgid.",
        "array" => "Atribuudil ei tohi olla rohkem kui: väärtusüksused."
    ],
    "max" => [
        "numeric" => "Atribuut ei pruugi olla suurem kui: Max.",
        "file" => "Atribuut ei pruugi olla suurem kui: Max Kilobytes.",
        "string" => "Atribuut ei pruugi olla suurem kui: max tähemärgid.",
        "array" => "Atribuudil ei pruugi olla rohkem kui: max üksused."
    ],
    "mimes" => "Atribuut peab olema tüüp :: väärtused.",
    "mimetypes" => "Atribuut peab olema tüüp :: väärtused.",
    "min" => [
        "numeric" => "Atribuut peab olema vähemalt: min.",
        "file" => "Atribuut peab olema vähemalt: min kilobüüdid.",
        "string" => "Atribuut peab olema vähemalt: minist tähemärki.",
        "array" => "Atribuudil peab olema vähemalt: min üksused."
    ],
    "not_in" => "Valitud: atribuut on kehtetu.",
    "not_regex" => "Atribuudi vorming on kehtetu.",
    "numeric" => "Atribuut peab olema number.",
    "password" => "Parool on vale.",
    "present" => "Atribuudi väli peab olema kohal.",
    "regex" => "Atribuudi vorming on kehtetu.",
    "required" => "Atribuudi väli on vajalik.",
    "required_if" => ": Atribuudi väli on vajalik, kui: muu on: väärtus.",
    "required_unless" => "Atribuudi väli on vajalik, kui: Muu on: väärtused.",
    "required_with" => "Atribuudi väli on vajalik, kui: väärtused on olemas.",
    "required_with_all" => ": Atribuudi väli on vajalik, kui: väärtused on olemas.",
    "required_without" => ": Atribuudi väli on vajalik, kui: väärtusi pole.",
    "required_without_all" => "Atribuudi väli on vajalik, kui ühtegi: väärtused pole olemas.",
    "same" => "Atribuut ja: Muu peab vastama.",
    "size" => [
        "numeric" => "Atribuut peab olema: suurus.",
        "file" => "Atribuut peab olema: Suurus kilobüüdid.",
        "string" => "Atribuut peab olema: suuruse tähemärgid.",
        "array" => "Atribuut peab sisaldama: suuruse üksusi."
    ],
    "starts_with" => "Atribuut peab algama ühest järgmistest :: väärtused.",
    "string" => "Atribuut peab olema string.",
    "timezone" => "Atribuut peab olema kehtiv tsoon.",
    "unique" => "Atribuut on juba võetud.",
    "uploaded" => "Atribuut ebaõnnestus üles laadida.",
    "url" => "Atribuudi vorming on kehtetu.",
    "uuid" => "Atribuut peab olema kehtiv UUID.",
    "custom" => [
        "attribute-name" => [
            "rule-name" => "eritellimusel"
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
