<?php
if (!class_exists('Texy', FALSE)) die();
class TexyLinkModule extends TexyModule implements ITexyPreBlock
{
    protected $default = array(
        'link/reference' => TRUE,
        'link/email' => TRUE,
        'link/url' => TRUE,
        'link/definition' => TRUE,
    );
    public $root = '';
    public $imageOnClick = 'return !popupImage(this.href)';  //
    public $popupOnClick = 'return !popup(this.href)';
    public $forceNoFollow = FALSE;
    protected $references = array();
    static private $deadlock;
    public function begin()
    {
        self::$deadlock = array();
        $tx = $this->texy;
        $tx->registerLinePattern(
            array($this, 'patternReference'),
            '#(\[[^\[\]\*\n'.TEXY_MARK.']+\])#U',
            'link/reference'
        );
        $tx->registerLinePattern(
            array($this, 'patternUrlEmail'),
            '#(?<=^|[\s(\[<:])(?:https?://|www\.|ftp://)[a-z0-9.-][/a-z\d+\.~%&?@=_:;\#,-]+[/\w\d+~%?@=_\#]#iu',
            'link/url'
        );
        $tx->registerLinePattern(
            array($this, 'patternUrlEmail'),
            '#(?<=^|[\s(\[\<:])'.TEXY_EMAIL.'#iu',
            'link/email'
        );
    }
    public function preBlock($text, $topLevel)
    {
        if ($topLevel && $this->texy->allowed['link/definition'])
            $text = preg_replace_callback(
                '#^\[([^\[\]\#\?\*\n]+)\]: +(\S+)(\ .+)?'.TEXY_MODIFIER.'?\s*()$#mUu',
                array($this, 'patternReferenceDef'),
                $text
            );
        return $text;
    }
    private function patternReferenceDef($matches)
    {
        list(, $mRef, $mLink, $mLabel, $mMod) = $matches;
        $link = new TexyLink($mLink);
        $link->label = trim($mLabel);
        $link->modifier->setProperties($mMod);
        $this->checkLink($link);
        $this->addReference($mRef, $link);
        return '';
    }
    public function patternReference($parser, $matches)
    {
        list(, $mRef) = $matches;
        $tx = $this->texy;
        $name = substr($mRef, 1, -1);
        $link = $this->getReference($name);
        if (!$link) {
            if (is_callable(array($tx->handler, 'newReference'))) {
                $res = $tx->handler->newReference($parser, $name);
                if ($res !== Texy::PROCEED) return $res;
            }
            return FALSE;
        }
        $link->type = TexyLink::BRACKET;
        if ($link->label != '') {  // NULL or ''
            if (isset(self::$deadlock[$link->name])) {
                $content = $link->label;
            } else {
                self::$deadlock[$link->name] = TRUE;
                $lineParser = new TexyLineParser($tx);
                $content = $lineParser->parse($link->label);
                unset(self::$deadlock[$link->name]);
            }
        } else {
            $content = $this->textualURL($link);
        }
        if (is_callable(array($tx->handler, 'linkReference'))) {
            $res = $tx->handler->linkReference($parser, $link, $content);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solve($link, $content);
    }
    public function patternUrlEmail($parser, $matches, $name)
    {
        list($mURL) = $matches;
        $link = new TexyLink($mURL);
        $this->checkLink($link);
        $content = $this->textualURL($link);
        $method = $name === 'link/email' ? 'linkEmail' : 'linkURL';
        if (is_callable(array($this->texy->handler, $method))) {
            $res = $this->texy->handler->$method($parser, $link, $content);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solve($link, $content);
    }
    public function addReference($name, TexyLink $link)
    {
        $link->name = TexyUtf::strtolower($name);
        $this->references[$link->name] = $link;
    }
    public function getReference($name)
    {
        $name = TexyUtf::strtolower($name);
        if (isset($this->references[$name])) {
            return clone $this->references[$name];
        } else {
            $pos = strpos($name, '?');
            if ($pos === FALSE) $pos = strpos($name, '#');
            if ($pos !== FALSE) { // try to extract ?... #... part
                $name2 = substr($name, 0, $pos);
                if (isset($this->references[$name2])) {
                    $link = clone $this->references[$name2];
                    $link->URL .= substr($name, $pos);
                    return $link;
                }
            }
        }
        return FALSE;
    }
    public function factoryLink($dest, $mMod, $label)
    {
        $tx = $this->texy;
        $type = TexyLink::COMMON;
        if (strlen($dest)>1 && $dest{0} === '[' && $dest{1} !== '*') {
            $type = TexyLink::BRACKET;
            $dest = substr($dest, 1, -1);
            $link = $this->getReference($dest);
        } elseif (strlen($dest)>1 && $dest{0} === '[' && $dest{1} === '*') {
            $type = TexyLink::IMAGE;
            $dest = trim(substr($dest, 2, -2));
            $image = $tx->imageModule->getReference($dest);
            if ($image) {
                $link = new TexyLink($image->linkedURL === NULL ? $image->URL : $image->linkedURL);
                $link->modifier = $image->modifier;
            }
        }
        if (empty($link)) {
            $link = new TexyLink(trim($dest));
            $this->checkLink($link);
        }
        if (strpos($link->URL, '%s') !== FALSE) {
            $link->URL = str_replace('%s', urlencode($tx->_toText($label)), $link->URL);
        }
        $link->modifier->setProperties($mMod);
        $link->type = $type;
        return $link;
    }
    public function solve($link, $content=NULL)
    {
        if ($link->URL == NULL) return $content;
        $tx = $this->texy;
        $el = TexyHtml::el('a');
        if (empty($link->modifier)) {
            $nofollow = $popup = FALSE;
        } else {
            $classes = array_flip($link->modifier->classes);
            $nofollow = isset($classes['nofollow']);
            $popup = isset($classes['popup']);
            unset($classes['nofollow'], $classes['popup']);
            $link->modifier->classes = array_flip($classes);
            $el->attrs['href'] = NULL; // trick - move to front
            $link->modifier->decorate($tx, $el);
        }
        if ($link->type === TexyLink::IMAGE) {
            $el->attrs['href'] = Texy::prependRoot($link->URL, $tx->imageModule->linkedRoot);
            $el->attrs['onclick'] = $this->imageOnClick;
        } else {
            $el->attrs['href'] = Texy::prependRoot($link->URL, $this->root);
            if ($nofollow || ($this->forceNoFollow && strpos($el->attrs['href'], '//') !== FALSE))
                $el->attrs['rel'] = 'nofollow';
        }
        if ($popup) $el->attrs['onclick'] = $this->popupOnClick;
        if ($content !== NULL) {
            if ($content instanceof TexyHtml)
                $el->addChild($content);
            else
                $el->setText($content);
        }
        $tx->summary['links'][] = $el->attrs['href'];
        return $el;
    }
    private function checkLink($link)
    {
        $tmp = $link->URL;
        if (strncasecmp($link->URL, 'www.', 4) === 0) {
            $link->URL = 'http://' . $link->URL;
        } elseif (preg_match('#'.TEXY_EMAIL.'$#iA', $link->URL)) {
            $link->URL = 'mailto:' . $link->URL;
        } elseif (!$this->texy->checkURL($link->URL, 'a')) {
            $link->URL = NULL;
        } else {
            $link->URL = str_replace('&amp;', '&', $link->URL); // replace unwanted &amp;
        }
        if ($link->URL !== $tmp) $link->raw = $tmp;
    }
    private function textualURL($link)
    {
        $URL = $link->raw === NULL ? $link->URL : $link->raw;
        if (preg_match('#^'.TEXY_EMAIL.'$#i', $URL)) { // email
            return $this->texy->obfuscateEmail
                   ? str_replace('@', $this->texy->protect("&#64;<!---->", Texy::CONTENT_MARKUP), $URL)
                   : $URL;
        }
        if (preg_match('#^(https?://|ftp://|www\.|/)#i', $URL)) {
            if (strncasecmp($URL, 'www.', 4) === 0) $parts = @parse_url('none://'.$URL);
            else $parts = @parse_url($URL);
            if ($parts === FALSE) return $URL;
            $res = '';
            if (isset($parts['scheme']) && $parts['scheme'] !== 'none')
                $res .= $parts['scheme'] . '://';
            if (isset($parts['host']))
                $res .= $parts['host'];
            if (isset($parts['path']))
                $res .=  (strlen($parts['path']) > 16 ? ('/...' . preg_replace('#^.*(.{0,12})$#U', '$1', $parts['path'])) : $parts['path']);
            if (isset($parts['query'])) {
                $res .= strlen($parts['query']) > 4 ? '?...' : ('?'.$parts['query']);
            } elseif (isset($parts['fragment'])) {
                $res .= strlen($parts['fragment']) > 4 ? '#...' : ('#'.$parts['fragment']);
            }
            return $res;
        }
        return $URL;
    }
} // TexyLinkModule
class TexyLink
{
    const
        COMMON = 1,
        BRACKET = 2,
        IMAGE = 3;
    public $URL;
    public $raw;
    public $modifier;
    public $type = TexyLink::COMMON;
    public $label;
    public $name;
    public function __construct($URL)
    {
        $this->URL = $URL;
        $this->modifier = new TexyModifier;
    }
    public function __clone()
    {
        if ($this->modifier)
            $this->modifier = clone $this->modifier;
    }
    function __get($nm) { throw new Exception("Undefined property '" . get_class($this) . "::$$nm'"); }
    function __set($nm, $val) { $this->__get($nm); }
}
