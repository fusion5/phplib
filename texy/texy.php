<?php
define('TEXY_DIR',  dirname(__FILE__).'/');
require_once TEXY_DIR.'libs/RegExp.Patterns.php';
require_once TEXY_DIR.'libs/TexyHtml.php';
require_once TEXY_DIR.'libs/TexyHtmlCleaner.php';
require_once TEXY_DIR.'libs/TexyModifier.php';
require_once TEXY_DIR.'libs/TexyModule.php';
require_once TEXY_DIR.'libs/TexyParser.php';
require_once TEXY_DIR.'libs/TexyUtf.php';
require_once TEXY_DIR.'modules/TexyParagraphModule.php';
require_once TEXY_DIR.'modules/TexyBlockModule.php';
require_once TEXY_DIR.'modules/TexyHeadingModule.php';
require_once TEXY_DIR.'modules/TexyHorizLineModule.php';
require_once TEXY_DIR.'modules/TexyHtmlModule.php';
require_once TEXY_DIR.'modules/TexyFigureModule.php';
require_once TEXY_DIR.'modules/TexyImageModule.php';
require_once TEXY_DIR.'modules/TexyLinkModule.php';
require_once TEXY_DIR.'modules/TexyListModule.php';
require_once TEXY_DIR.'modules/TexyLongWordsModule.php';
require_once TEXY_DIR.'modules/TexyPhraseModule.php';
require_once TEXY_DIR.'modules/TexyQuoteModule.php';
require_once TEXY_DIR.'modules/TexyScriptModule.php';
require_once TEXY_DIR.'modules/TexyEmoticonModule.php';
require_once TEXY_DIR.'modules/TexyTableModule.php';
require_once TEXY_DIR.'modules/TexyTypographyModule.php';
class Texy
{
    const ALL = TRUE;
    const NONE = FALSE;
    const VERSION = '2.0 RC 1 (Revision: 131, Date: 2007/06/04 00:48:25)';
    const CONTENT_MARKUP = "\x17";
    const CONTENT_REPLACED = "\x16";
    const CONTENT_TEXTUAL = "\x15";
    const CONTENT_BLOCK = "\x14";
    const PROCEED = NULL;
    public $encoding = 'utf-8';
    public $allowed = array();
    public $allowedTags;
    public $allowedClasses = Texy::ALL; // all classes and id are allowed
    public $allowedStyles = Texy::ALL;  // all inline styles are allowed
    public $tabWidth = 8;
    public $obfuscateEmail = TRUE;
    public $urlSchemeFilters = NULL; // disable URL scheme filter
    public $summary = array(
        'images' => array(),
        'links' => array(),
        'preload' => array(),
    );
    public $styleSheet = '';
    public $mergeLines = TRUE;
    public $handler;
    public $ignoreEmptyStuff = TRUE;
    static public $strictDTD = FALSE;
    public
        $scriptModule,
        $paragraphModule,
        $htmlModule,
        $imageModule,
        $linkModule,
        $phraseModule,
        $emoticonModule,
        $blockModule,
        $headingModule,
        $horizLineModule,
        $quoteModule,
        $listModule,
        $tableModule,
        $figureModule,
        $typographyModule,
        $longWordsModule;
    public
        $cleaner;
    private $linePatterns = array();
    private $blockPatterns = array();
    private $DOM;
    private $modules;
    private $marks = array();
    public $_classes, $_styles;
    public $_preBlockModules;
    private $_state = 0;
    public function __construct()
    {
        $this->loadModules();
        $this->cleaner = new TexyHtmlCleaner($this);
        foreach (TexyHtmlCleaner::$dtd as $tag => $dtd)
            $this->allowedTags[$tag] = is_array($dtd[0]) ? array_keys($dtd[0]) : $dtd[0];
        $link = new TexyLink('http://texy.info/');
        $link->modifier->title = 'The best text -> HTML converter and formatter';
        $link->label = 'Texy!';
        $this->linkModule->addReference('texy', $link);
        $link = new TexyLink('http://www.google.com/search?q=%s');
        $this->linkModule->addReference('google', $link);
        $link = new TexyLink('http://en.wikipedia.org/wiki/Special:Search?search=%s');
        $this->linkModule->addReference('wikipedia', $link);
        if (function_exists('mb_get_info')) {
            $mb = mb_get_info();
            if ($mb['func_overload'] & 2 && $mb['internal_encoding'][0] === 'U') { // U??
                mb_internal_encoding('pass');
                trigger_error('Texy: mb_internal_encoding changed to pass', E_USER_WARNING);
            }
        }
    }
    protected function loadModules()
    {
        $this->scriptModule = new TexyScriptModule($this);
        $this->htmlModule = new TexyHtmlModule($this);
        $this->imageModule = new TexyImageModule($this);
        $this->phraseModule = new TexyPhraseModule($this);
        $this->linkModule = new TexyLinkModule($this);
        $this->emoticonModule = new TexyEmoticonModule($this);
        $this->paragraphModule = new TexyParagraphModule($this);
        $this->blockModule = new TexyBlockModule($this);
        $this->headingModule = new TexyHeadingModule($this);
        $this->horizLineModule = new TexyHorizLineModule($this);
        $this->quoteModule = new TexyQuoteModule($this);
        $this->listModule = new TexyListModule($this);
        $this->tableModule = new TexyTableModule($this);
        $this->figureModule = new TexyFigureModule($this);
        $this->typographyModule = new TexyTypographyModule($this);
        $this->longWordsModule = new TexyLongWordsModule($this);
    }
    public function registerModule(TexyModule $module)
    {
        $this->modules[] = $module;
    }
    public function registerLinePattern($handler, $pattern, $name)
    {
        if (empty($this->allowed[$name])) return;
        $this->linePatterns[$name] = array(
            'handler'     => $handler,
            'pattern'     => $pattern,
        );
    }
    public function registerBlockPattern($handler, $pattern, $name)
    {
        if (empty($this->allowed[$name])) return;
        $this->blockPatterns[$name] = array(
            'handler'     => $handler,
            'pattern'     => $pattern  . 'm',  // force multiline
        );
    }
    public function process($text, $singleLine=FALSE)
    {
        $this->parse($text, $singleLine);
        return $this->toHtml();
    }
    public function processTypo($text)
    {
        $text = TexyUtf::toUtf($text, $this->encoding);
        $text = self::normalize($text);
        $this->typographyModule->begin();
        $text = $this->typographyModule->postLine($text);
        return $text;
    }
    public function parse($text, $singleLine=FALSE)
    {
        if ($this->_state === 1)
            throw new Exception('Parsing is in progress yet.');
        if ($this->handler && !is_object($this->handler))
            throw new Exception('$texy->handler must be object. See documentation.');
        $this->marks = array();
        $this->_state = 1;
        if (is_array($this->allowedClasses)) $this->_classes = array_flip($this->allowedClasses);
        else $this->_classes = $this->allowedClasses;
        if (is_array($this->allowedStyles)) $this->_styles = array_flip($this->allowedStyles);
        else $this->_styles = $this->allowedStyles;
        $tmp = array($this->linePatterns, $this->blockPatterns);
        $text = TexyUtf::toUtf($text, $this->encoding);
        $text = self::normalize($text);
        while (strpos($text, "\t") !== FALSE)
            $text = preg_replace_callback('#^(.*)\t#mU', array($this, 'tabCb'), $text);
        $this->_preBlockModules = array();
        foreach ($this->modules as $module) {
            $module->begin();
            if ($module instanceof ITexyPreBlock) $this->_preBlockModules[] = $module;
        }
        $this->DOM = TexyHtml::el();
        if ($singleLine)
            $this->DOM->parseLine($this, $text);
        else
            $this->DOM->parseBlock($this, $text, TRUE);
        if (is_callable(array($this->handler, 'afterParse')))
            $this->handler->afterParse($this, $this->DOM, $singleLine);
        list($this->linePatterns, $this->blockPatterns) = $tmp;
        $this->_state = 2;
    }
    public function toHtml()
    {
        if ($this->_state !== 2) throw new Exception('Call $texy->parse() first.');
        $html = $this->_toHtml( $this->DOM->export($this) );
        if (!defined('TEXY_NOTICE_SHOWED')) {
            $html .= "\n<!-- by Texy2! -->";
            define('TEXY_NOTICE_SHOWED', TRUE);
        }
        $html = TexyUtf::utf2html($html, $this->encoding);
        return $html;
    }
    public function toText()
    {
        if ($this->_state !== 2) throw new Exception('Call $texy->parse() first.');
        $text = $this->_toText( $this->DOM->export($this) );
        $text = TexyUtf::utfTo($text, $this->encoding);
        return $text;
    }
    public function _toHtml($s)
    {
        $s = self::unescapeHtml($s);
        $blocks = explode(self::CONTENT_BLOCK, $s);
        foreach ($this->modules as $module) {
            if ($module instanceof ITexyPostLine) {
                foreach ($blocks as $n => $s) {
                    if ($n % 2 === 0 && $s !== '')
                        $blocks[$n] = $module->postLine($s);
                }
            }
        }
        $s = implode(self::CONTENT_BLOCK, $blocks);
        $s = self::escapeHtml($s);
        $s = $this->unProtect($s);
        $s = $this->cleaner->process($s);
        $s = self::unfreezeSpaces($s);
        return $s;
    }
    public function _toText($s)
    {
        $save = $this->cleaner->lineWrap;
        $this->cleaner->lineWrap = FALSE;
        $s = $this->_toHtml( $s );
        $this->cleaner->lineWrap = $save;
        $s = preg_replace('#<(script|style)(.*)</\\1>#Uis', '', $s);
        $s = strip_tags($s);
        $s = preg_replace('#\n\s*\n\s*\n[\n\s]*\n#', "\n\n", $s);
        $s = Texy::unescapeHtml($s);
        $s = strtr($s, array(
            "\xC2\xAD" => '',  // shy
            "\xC2\xA0" => ' ', // nbsp
        ));
        return $s;
    }
    public function safeMode()
    {
        trigger_error('$texy->safeMode() is deprecated. Use TexyConfigurator::safeMode($texy)', E_USER_WARNING);
        TexyConfigurator::safeMode($this);
    }
    public function trustMode()
    {
        trigger_error('$texy->trustMode() is deprecated. Use TexyConfigurator::trustMode($texy)', E_USER_WARNING);
        TexyConfigurator::trustMode($this);
    }
    static public function freezeSpaces($s)
    {
        return strtr($s, " \t\r\n", "\x01\x02\x03\x04");
    }
    static public function unfreezeSpaces($s)
    {
        return strtr($s, "\x01\x02\x03\x04", " \t\r\n");
    }
    static public function normalize($s)
    {
        $s = preg_replace('#[\x01-\x04\x14-\x1F]+#', '', $s);
        $s = str_replace("\r\n", "\n", $s); // DOS
        $s = strtr($s, "\r", "\n"); // Mac
        $s = preg_replace("#[\t ]+$#m", '', $s); // right trim
        $s = trim($s, "\n");
        return $s;
    }
    static public function webalize($s, $charlist=NULL)
    {
        $s = TexyUtf::utf2ascii($s);
        $s = strtolower($s);
        if ($charlist) $charlist = preg_quote($charlist, '#');
        $s = preg_replace('#[^a-z0-9'.$charlist.']+#', '-', $s);
        $s = trim($s, '-');
        return $s;
    }
    static public function escapeHtml($s)
    {
        return str_replace(array('&', '<', '>'), array('&amp;', '&lt;', '&gt;'), $s);
    }
    static public function unescapeHtml($s)
    {
        if (strpos($s, '&') === FALSE) return $s;
        return html_entity_decode($s, ENT_QUOTES, 'UTF-8');
    }
    public function protect($child, $contentType=self::CONTENT_BLOCK)
    {
        if ($child==='') return '';
        $key = $contentType
            . strtr(base_convert(count($this->marks), 10, 8), '01234567', "\x18\x19\x1A\x1B\x1C\x1D\x1E\x1F")
            . $contentType;
        $this->marks[$key] = $child;
        return $key;
    }
    public function unProtect($html)
    {
        return strtr($html, $this->marks);
    }
    public function checkURL($URL, $type)
    {
        if (!empty($this->urlSchemeFilters[$type])
            && preg_match('#'.TEXY_URLSCHEME.'#iA', $URL)
            && !preg_match($this->urlSchemeFilters[$type], $URL))
            return FALSE;
        return TRUE;
    }
    static public function isRelative($URL)
    {
        return !preg_match('#'.TEXY_URLSCHEME.'|[\#/?]#iA', $URL);
    }
    static public function prependRoot($URL, $root)
    {
        if ($root == NULL || !self::isRelative($URL)) return $URL;
        return rtrim($root, '/\\') . '/' . $URL;
    }
    public function getLinePatterns()
    {
        return $this->linePatterns;
    }
    public function getBlockPatterns()
    {
        return $this->blockPatterns;
    }
    public function getDOM()
    {
        return $this->DOM;
    }
    private function tabCb($m)
    {
        return $m[1] . str_repeat(' ', $this->tabWidth - strlen($m[1]) % $this->tabWidth);
    }
    public function free()
    {
        foreach (array_keys(get_object_vars($this)) as $key)
            $this->$key = NULL;
    }
    public function __clone() { throw new Exception("Clone is not supported."); }
    function __get($nm) { throw new Exception("Undefined property '" . get_class($this) . "::$$nm'"); }
    function __set($nm, $val) { $this->__get($nm); }
} // Texy
class TexyConfigurator
{
    static public $safeTags = array(
        'a'         => array('href', 'title'),
        'acronym'   => array('title'),
        'b'         => array(),
        'br'        => array(),
        'cite'      => array(),
        'code'      => array(),
        'em'        => array(),
        'i'         => array(),
        'strong'    => array(),
        'sub'       => array(),
        'sup'       => array(),
        'q'         => array(),
        'small'     => array(),
    );
    static public function safeMode(Texy $texy)
    {
        $texy->allowedClasses = Texy::NONE;                 // no class or ID are allowed
        $texy->allowedStyles  = Texy::NONE;                 // style modifiers are disabled
        $texy->allowedTags = self::$safeTags;               // only some "safe" HTML tags and attributes are allowed
        $texy->urlSchemeFilters['a'] = '#https?:|ftp:|mailto:#A';
        $texy->urlSchemeFilters['i'] = '#https?:#A';
        $texy->urlSchemeFilters['c'] = '#http:#A';
        $texy->allowed['image'] = FALSE;                    // disable images
        $texy->allowed['link/definition'] = FALSE;          // disable [ref]: URL  reference definitions
        $texy->allowed['html/comment'] = FALSE;             // disable HTML comments
        $texy->linkModule->forceNoFollow = TRUE;            // force rel="nofollow"
    }
    static public function trustMode(Texy $texy)
    {
        $texy->allowedClasses = Texy::ALL;                  // classes and id are allowed
        $texy->allowedStyles  = Texy::ALL;                  // inline styles are allowed
        $texy->allowedTags = array();                       // all valid HTML tags
        foreach (TexyHtmlCleaner::$dtd as $tag => $dtd)
            $texy->allowedTags[$tag] = is_array($dtd[0]) ? array_keys($dtd[0]) : $dtd[0];
        $texy->urlSchemeFilters = NULL;                     // disable URL scheme filter
        $texy->allowed['image'] = TRUE;                     // enable images
        $texy->allowed['link/definition'] = TRUE;           // enable [ref]: URL  reference definitions
        $texy->allowed['html/comment'] = TRUE;              // enable HTML comments
        $texy->linkModule->forceNoFollow = FALSE;           // disable automatic rel="nofollow"
    }
}
