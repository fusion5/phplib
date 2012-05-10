<?php
if (!class_exists('Texy', FALSE)) die();
class TexyEmoticonModule extends TexyModule
{
    protected $default = array('emoticon' => FALSE);
    public $icons = array (
        ':-)'  =>  'smile.gif',
        ':-('  =>  'sad.gif',
        ';-)'  =>  'wink.gif',
        ':-D'  =>  'biggrin.gif',
        '8-O' => 'eek.gif',
        '8-)'  =>  'cool.gif',
        ':-?'  =>  'confused.gif',
        ':-x' => 'mad.gif',
        ':-P' => 'razz.gif',
        ':-|'  =>  'neutral.gif',
    );
    public $class;
    public $root;
    public $fileRoot;
    public function begin()
    {
        if (empty($this->texy->allowed['emoticon'])) return;
        krsort($this->icons);
        $pattern = array();
        foreach ($this->icons as $key => $foo)
            $pattern[] = preg_quote($key, '#') . '+'; // last char can be repeated
        $this->texy->registerLinePattern(
            array($this, 'pattern'),
            '#(?<=^|[\\x00-\\x20])(' . implode('|', $pattern) . ')#',
            'emoticon'
        );
    }
    public function pattern($parser, $matches)
    {
        $match = $matches[0];
        $tx = $this->texy;
        foreach ($this->icons as $emoticon => $foo)
        {
            if (strncmp($match, $emoticon, strlen($emoticon)) === 0)
            {
                if (is_callable(array($tx->handler, 'emoticon'))) {
                    $res = $tx->handler->emoticon($parser, $emoticon, $match);
                    if ($res !== Texy::PROCEED) return $res;
                }
                return $this->solve($emoticon, $match);
            }
        }
        return FALSE; // tohle se nestane
    }
    public function solve($emoticon, $raw)
    {
        $tx = $this->texy;
        $file = $this->icons[$emoticon];
        $el = TexyHtml::el('img');
        $el->attrs['src'] = Texy::prependRoot($file, $this->root === NULL ?  $tx->imageModule->root : $this->root);
        $el->attrs['alt'] = $raw;
        $el->attrs['class'][] = $this->class;
        $file = rtrim($this->fileRoot === NULL ?  $tx->imageModule->fileRoot : $this->fileRoot, '/\\') . '/' . $file;
        if (is_file($file)) {
            $size = getImageSize($file);
            if (is_array($size)) {
                $el->attrs['width'] = $size[0];
                $el->attrs['height'] = $size[1];
            }
        }
        $tx->summary['images'][] = $el->attrs['src'];
        return $el;
    }
}
