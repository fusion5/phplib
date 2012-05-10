<?php
if (!class_exists('Texy', FALSE)) die();
class TexyHeadingModule extends TexyModule
{
    const
        DYNAMIC = 1,  // auto-leveling
        FIXED =   2;  // fixed-leveling
    protected $default = array('heading/surrounded' => TRUE, 'heading/underlined' => TRUE);
    public $title;
    public $TOC;
    public $generateID = FALSE;
    public $idPrefix = 'toc-';
    public $top = 1;
    public $balancing = TexyHeadingModule::DYNAMIC;
    public $levels = array(
        '#' => 0,  //  #  -->  $levels['#'] + $top = 0 + 1 = 1  --> <h1> ... </h1>
        '*' => 1,
        '=' => 2,
        '-' => 3,
    );
    private $usedID;
    private $dynamicMap;
    private $dynamicTop;
    public function begin()
    {
        $this->texy->registerBlockPattern(
            array($this, 'patternUnderline'),
            '#^(\S.*)'.TEXY_MODIFIER_H.'?\n'
          . '(\#{3,}|\*{3,}|={3,}|-{3,})$#mU',
            'heading/underlined'
        );
        $this->texy->registerBlockPattern(
            array($this, 'patternSurround'),
            '#^(\#{2,}+|={2,}+)(.+)'.TEXY_MODIFIER_H.'?()$#mU',
            'heading/surrounded'
        );
        $this->title = NULL;
        $this->usedID = array();
        $this->TOC = array();
        $foo1 = array(); $this->dynamicMap = & $foo1;
        $foo2 = -100; $this->dynamicTop = & $foo2;
    }
    public function patternUnderline($parser, $matches)
    {
        list(, $mContent, $mMod, $mLine) = $matches;
        $mod = new TexyModifier($mMod);
        $level = $this->levels[$mLine[0]];
        if (is_callable(array($this->texy->handler, 'heading'))) {
            $res = $this->texy->handler->heading($parser, $level, $mContent, $mod, FALSE);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solve($level, $mContent, $mod, FALSE);
    }
    public function patternSurround($parser, $matches)
    {
        list(, $mLine, $mContent, $mMod) = $matches;
        $mod = new TexyModifier($mMod);
        $level = 7 - min(7, max(2, strlen($mLine)));
        $mContent = rtrim($mContent, $mLine[0] . ' ');
        if (is_callable(array($this->texy->handler, 'heading'))) {
            $res = $this->texy->handler->heading($parser, $level, $mContent, $mod, TRUE);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solve($level, $mContent, $mod, TRUE);
    }
    public function solve($level, $content, $mod, $isSurrounded)
    {
        $tx = $this->texy;
        $el = new TexyHeadingElement;
        $mod->decorate($tx, $el);
        $el->level = $level;
        $el->top = $this->top;
        if ($this->balancing === self::DYNAMIC) {
            if ($isSurrounded) {
                $this->dynamicTop = max($this->dynamicTop, $this->top - $level);
                $el->top = & $this->dynamicTop;
            } else {
                $this->dynamicMap[$level] = $level;
                $el->map = & $this->dynamicMap;
            }
        }
        $el->parseLine($tx, trim($content));
        $title = $tx->_toText($el->getText());
        if ($this->title === NULL) $this->title = $title;
        if ($this->generateID && empty($el->attrs['id'])) {
            $id = $this->idPrefix . Texy::webalize($title);
            $counter = '';
            if (isset($this->usedID[$id . $counter])) {
                $counter = 2;
                while (isset($this->usedID[$id . '-' . $counter])) $counter++;
                $id .= '-' . $counter;
            }
            $this->usedID[$id] = TRUE;
            $el->attrs['id'] = $id;
        }
        $TOC = array(
            'id' => isset($el->attrs['id']) ? $el->attrs['id'] : NULL,
            'title' => $title,
            'level' => 0,
        );
        $this->TOC[] = & $TOC;
        $el->TOC = & $TOC;
        return $el;
    }
} // TexyHeadingModule
class TexyHeadingElement extends TexyHtml
{
    public $name = 'h?';
    public $level;
    public $top;
    public $map;
    public $TOC;
    public function startTag()
    {
        $level = $this->level;
        if ($this->map) {
            asort($this->map);
            $level = array_search($level, array_values($this->map), TRUE);
        }
        $level += $this->top;
        $this->name = 'h' . min(6, max(1, $level));
        $this->TOC['level'] = $level;
        return parent::startTag();
    }
} // TexyHeadingElement
