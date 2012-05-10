<?php
if (!class_exists('Texy', FALSE)) die();
class TexyTypographyModule extends TexyModule implements ITexyPostLine
{
    protected $default = array('typography' => TRUE);
    static public $locales = array(
        'cs' => array(
            'singleQuotes' => array("\xe2\x80\x9a", "\xe2\x80\x98"), // U+201A, U+2018
            'doubleQuotes' => array("\xe2\x80\x9e", "\xe2\x80\x9c"), // U+201E, U+201C
        ),
        'en' => array(
            'singleQuotes' => array("\xe2\x80\x98", "\xe2\x80\x99"), // U+2018, U+2019
            'doubleQuotes' => array("\xe2\x80\x9c", "\xe2\x80\x9d"), // U+201C, U+201D
        ),
        'fr' => array(
            'singleQuotes' => array("\xe2\x80\xb9", "\xe2\x80\xba"), // U+2039, U+203A
            'doubleQuotes' => array("\xc2\xab", "\xc2\xbb"),         // U+00AB, U+00BB
        ),
        'de' => array(
            'singleQuotes' => array("\xe2\x80\x9a", "\xe2\x80\x98"), // U+201A, U+2018
            'doubleQuotes' => array("\xe2\x80\x9e", "\xe2\x80\x9c"), // U+201E, U+201C
        ),
        'pl' => array(
            'singleQuotes' => array("\xe2\x80\x9a", "\xe2\x80\x99"), // U+201A, U+2019
            'doubleQuotes' => array("\xe2\x80\x9e", "\xe2\x80\x9d"), // U+201E, U+201D
        ),
    );
    public $locale = 'cs';
    private $pattern, $replace;
    public function begin()
    {
        if (isset(self::$locales[$this->locale]))
            $locale = self::$locales[$this->locale];
        else // fall back
            $locale = self::$locales['en'];
        $pairs = array(
            '#(?<![.\x{2026}])\.{3,4}(?![.\x{2026}])#mu' => "\xe2\x80\xa6",                // ellipsis  ...
            '#(?<=[\d ])-(?=[\d ])#'                  => "\xe2\x80\x93",                   // en dash  -
            '#,-#'                                    => ",\xe2\x80\x93",                  // en dash ,-
            '#(?<!\d)(\d{1,2}\.) (\d{1,2}\.) (\d\d)#' => "\$1\xc2\xa0\$2\xc2\xa0\$3",      // date 23. 1. 1978
            '#(?<!\d)(\d{1,2}\.) (\d{1,2}\.)#'        => "\$1\xc2\xa0\$2",                 // date 23. 1.
            '#([\x{2013}\x{2014}]) #u'                => "\$1\xc2\xa0",                    // dash &nbsp;
            '# --- #'                                 => " \xe2\x80\x94\xc2\xa0",          // em dash ---
            '# -- #'                                  => " \xe2\x80\x93\xc2\xa0",          // en dash --
            '# <-{1,2}> #'                            => " \xe2\x86\x94 ",                 // left right arrow <-->
            '#-{1,}> #'                               => " \xe2\x86\x92 ",                 // right arrow -->
            '# <-{1,}#'                               => " \xe2\x86\x90 ",                 // left arrow <--
            '#={1,}> #'                               => " \xe2\x87\x92 ",                 // right arrow ==>
            '#(\d+)( ?)x\\2(\d+)\\2x\\2(\d+)#'        => "\$1\xc3\x97\$3\xc3\x97\$4",      // dimension sign x
            '#(\d+)( ?)x\\2(\d+)#'                    => "\$1\xc3\x97\$3",                 // dimension sign x
            '#(?<=\d)x(?= |,|.|$)#m'                  => "\xc3\x97",                       // 10x
            '#(\S ?)\(TM\)#i'                         => "\$1\xe2\x84\xa2",                // trademark  (TM)
            '#(\S ?)\(R\)#i'                          => "\$1\xc2\xae",                    // registered (R)
            '#\(C\)( ?\S)#i'                          => "\xc2\xa9\$1",                    // copyright  (C)
            '#\(EUR\)#'                               => "\xe2\x82\xac",                   // Euro  (EUR)
            '#(\d{1,3}) (\d{3}) (\d{3}) (\d{3})#'     => "\$1\xc2\xa0\$2\xc2\xa0\$3\xc2\xa0\$4", // (phone) number 1 123 123 123
            '#(\d{1,3}) (\d{3}) (\d{3})#'             => "\$1\xc2\xa0\$2\xc2\xa0\$3",      // (phone) number 1 123 123
            '#(\d{1,3}) (\d{3})#'                     => "\$1\xc2\xa0\$2",                 // number 1 123
            '#(?<=[^\s\x17])\s+([\x17-\x1F]+)(?=\s)#u'=> "\$1",                            // remove intermarkup space phase 1
            '#(?<=\s)([\x17-\x1F]+)\s+#u'             => "\$1",                            // remove intermarkup space phase 2
            '#(?<=.{50})\s+(?=[\x17-\x1F]*\S{1,6}[\x17-\x1F]*$)#us' => "\xc2\xa0",         // space before last short word
            '#(?<=^| |\.|,|-|\+|\x16)([\x17-\x1F]*\d+[\x17-\x1F]*)\s+([\x17-\x1F]*['.TEXY_CHAR.'\x{b0}-\x{be}\x{2020}-\x{214f}])#mu'
                                                      => "\$1\xc2\xa0\$2",
            '#(?<=^|[^0-9'.TEXY_CHAR.'])([\x17-\x1F]*[ksvzouiKSVZOUIA][\x17-\x1F]*)\s+([\x17-\x1F]*[0-9'.TEXY_CHAR.'])#mus'
                                                      => "\$1\xc2\xa0\$2",
            '#(?<!"|\w)"(?!\ |")(.+)(?<!\ |")"(?!")()#U' => $locale['doubleQuotes'][0].'$1'.$locale['doubleQuotes'][1], // double ""
            '#(?<!\'|\w)\'(?!\ |\')(.+)(?<!\ |\')\'(?!\')()#Uu' => $locale['singleQuotes'][0].'$1'.$locale['singleQuotes'][1], // single ''
        );
        $this->pattern = array_keys($pairs);
        $this->replace = array_values($pairs);
    }
    public function postLine($text)
    {
        if (empty($this->texy->allowed['typography'])) return $text;
        return preg_replace($this->pattern, $this->replace, $text);
    }
} // TexyTypographyModule
