<?php
if (!class_exists('Texy', FALSE)) die();
define('TEXY_CHAR',        'A-Za-z\x{C0}-\x{2FF}\x{370}-\x{1EFF}');
define('TEXY_MARK',        "\x14-\x1F");
define('TEXY_MODIFIER',    '(?: *(?<= |^)\\.((?:\\([^)\\n]+\\)|\\[[^\\]\\n]+\\]|\\{[^}\\n]+\\}){1,3}?))');
define('TEXY_MODIFIER_H',  '(?: *(?<= |^)\\.((?:\\([^)\\n]+\\)|\\[[^\\]\\n]+\\]|\\{[^}\\n]+\\}|<>|>|=|<){1,4}?))');
define('TEXY_MODIFIER_HV', '(?: *(?<= |^)\\.((?:\\([^)\\n]+\\)|\\[[^\\]\\n]+\\]|\\{[^}\\n]+\\}|<>|>|=|<|\\^|\\-|\\_){1,5}?))');
define('TEXY_IMAGE',       '\[\*([^\n'.TEXY_MARK.']+)'.TEXY_MODIFIER.'? *(\*|>|<)\]');
define('TEXY_LINK_URL',    '(?:\[[^\]\n]+\]|(?!\[)[^\s'.TEXY_MARK.']*?[^:);,.!?\s'.TEXY_MARK.'])'); // any url (nekonèí :).,!?
define('TEXY_LINK',        '(?::('.TEXY_LINK_URL.'))');       // any link
define('TEXY_LINK_N',      '(?::('.TEXY_LINK_URL.'|:))');     // any link (also unstated)
define('TEXY_EMAIL',       '[a-z0-9.+_-]+@[a-z0-9.+_-]+\.[a-z]{2,}');    // name@exaple.com
define('TEXY_URLSCHEME',   '[a-z][a-z0-9+.-]*:');    // http:  |  mailto:
