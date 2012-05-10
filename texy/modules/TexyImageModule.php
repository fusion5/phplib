<?php
if (!class_exists('Texy', FALSE)) die();
class TexyImageModule extends TexyModule implements ITexyPreBlock
{
    protected $default = array(
        'image' => TRUE,
        'image/definition' => TRUE,
    );
    public $root = 'images/';
    public $linkedRoot = 'images/';
    public $fileRoot = 'images/';
    public $leftClass;
    public $rightClass;
    public $defaultAlt = '';
    public $onLoad = "var i=new Image();i.src='%i';if(typeof preload=='undefined')preload=new Array();preload[preload.length]=i;this.onload=''";
    private $references = array();
    public function __construct($texy)
    {
        parent::__construct($texy);
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $this->fileRoot = dirname($_SERVER['SCRIPT_FILENAME']) . '/' . $this->root;
        }
    }
    public function begin()
    {
        $this->texy->registerLinePattern(
            array($this, 'patternImage'),
            '#'.TEXY_IMAGE.TEXY_LINK_N.'??()#Uu',
            'image'
        );
    }
    public function preBlock($text, $topLevel)
    {
        if ($topLevel && $this->texy->allowed['image/definition'])
           $text = preg_replace_callback(
               '#^\[\*([^\n]+)\*\]:\ +(.+)\ *'.TEXY_MODIFIER.'?\s*()$#mUu',
               array($this, 'patternReferenceDef'),
               $text
           );
        return $text;
    }
    public function patternReferenceDef($matches)
    {
        list(, $mRef, $mURLs, $mMod) = $matches;
        $image = $this->factoryImage($mURLs, $mMod, FALSE);
        $this->addReference($mRef, $image);
        return '';
    }
    public function patternImage($parser, $matches)
    {
        list(, $mURLs, $mMod, $mAlign, $mLink) = $matches;
        $tx = $this->texy;
        $image = $this->factoryImage($mURLs, $mMod.$mAlign);
        if ($mLink) {
            if ($mLink === ':') {
                $link = new TexyLink($image->linkedURL === NULL ? $image->URL : $image->linkedURL);
                $link->raw = ':';
                $link->type = TexyLink::IMAGE;
            } else {
                $link = $tx->linkModule->factoryLink($mLink, NULL, NULL);
            }
        } else $link = NULL;
        if (is_callable(array($tx->handler, 'image'))) {
            $res = $tx->handler->image($parser, $image, $link);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solve($image, $link);
    }
    public function addReference($name, TexyImage $image)
    {
        $image->name = TexyUtf::strtolower($name);
        $this->references[$image->name] = $image;
    }
    public function getReference($name)
    {
        $name = TexyUtf::strtolower($name);
        if (isset($this->references[$name]))
            return clone $this->references[$name];
        return FALSE;
    }
    public function factoryImage($content, $mod, $tryRef=TRUE)
    {
        $image = $tryRef ? $this->getReference(trim($content)) : FALSE;
        if (!$image) {
            $tx = $this->texy;
            $content = explode('|', $content);
            $image = new TexyImage;
            $matches = NULL;
            if (preg_match('#^(.*) (?:(\d+)|\?) *x *(?:(\d+)|\?) *()$#U', $content[0], $matches)) {
                $image->URL = trim($matches[1]);
                $image->width = (int) $matches[2];
                $image->height = (int) $matches[3];
            } else {
                $image->URL = trim($content[0]);
            }
            if (!$tx->checkURL($image->URL, 'i')) $image->URL = NULL;
            if (isset($content[1])) {
                $tmp = trim($content[1]);
                if ($tmp !== '' && $tx->checkURL($tmp, 'i')) $image->overURL = $tmp;
            }
            if (isset($content[2])) {
                $tmp = trim($content[2]);
                if ($tmp !== '' && $tx->checkURL($tmp, 'a')) $image->linkedURL = $tmp;
            }
        }
        $image->modifier->setProperties($mod);
        return $image;
    }
    public function solve(TexyImage $image, $link)
    {
        if ($image->URL == NULL) return FALSE;
        $tx = $this->texy;
        $mod = $image->modifier;
        $alt = $mod->title;
        $mod->title = NULL;
        $hAlign = $mod->hAlign;
        $mod->hAlign = NULL;
        $el = TexyHtml::el('img');
        $el->attrs['src'] = NULL; // trick - move to front
        $mod->decorate($tx, $el);
        $el->attrs['src'] = Texy::prependRoot($image->URL, $this->root);
        if (!isset($el->attrs['alt'])) {
            if ($alt !== NULL) $el->attrs['alt'] = $tx->typographyModule->postLine($alt);
            else $el->attrs['alt'] = $this->defaultAlt;
        }
        if ($hAlign === TexyModifier::HALIGN_LEFT) {
            if ($this->leftClass != '')
                $el->attrs['class'][] = $this->leftClass;
            else
                $el->attrs['style']['float'] = 'left';
        } elseif ($hAlign === TexyModifier::HALIGN_RIGHT)  {
            if ($this->rightClass != '')
                $el->attrs['class'][] = $this->rightClass;
            else
                $el->attrs['style']['float'] = 'right';
        }
        if ($image->width || $image->height) {
            $el->attrs['width'] = $image->width;
            $el->attrs['height'] = $image->height;
        } else {
            if (Texy::isRelative($image->URL) && strpos($image->URL, '..') === FALSE) {
                $file = rtrim($this->fileRoot, '/\\') . '/' . $image->URL;
                if (is_file($file)) {
                    $size = getImageSize($file);
                    if (is_array($size)) {
                        $image->width = $el->attrs['width'] = $size[0];
                        $image->height = $el->attrs['height'] = $size[1];
                    }
                }
            }
        }
        if ($image->overURL !== NULL) {
            $overSrc = Texy::prependRoot($image->overURL, $this->root);
            $el->attrs['onmouseover'] = 'this.src=\'' . addSlashes($overSrc) . '\'';
            $el->attrs['onmouseout'] = 'this.src=\'' . addSlashes($el->attrs['src']) . '\'';
            $el->attrs['onload'] = str_replace('%i', addSlashes($overSrc), $this->onLoad);
            $tx->summary['preload'][] = $overSrc;
        }
        $tx->summary['images'][] = $el->attrs['src'];
        if ($link) return $tx->linkModule->solve($link, $el);
        return $el;
    }
} // TexyImageModule
class TexyImage
{
    public $URL;
    public $overURL;
    public $linkedURL;
    public $width;
    public $height;
    public $modifier;
    public $name;
    public function __construct()
    {
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
