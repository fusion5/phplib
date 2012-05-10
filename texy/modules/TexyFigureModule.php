<?php
if (!class_exists('Texy', FALSE)) die();
class TexyFigureModule extends TexyModule
{
    protected $default = array('figure' => TRUE);
    public $class = 'figure';
    public $leftClass;
    public $rightClass;
    public $widthDelta = 10;
    public function begin()
    {
        $this->texy->registerBlockPattern(
            array($this, 'pattern'),
            '#^'.TEXY_IMAGE.TEXY_LINK_N.'?? +\*\*\* +(.*)'.TEXY_MODIFIER_H.'?()$#mUu',
            'figure'
        );
    }
    public function pattern($parser, $matches)
    {
        list(, $mURLs, $mImgMod, $mAlign, $mLink, $mContent, $mMod) = $matches;
        $tx = $this->texy;
        $image = $tx->imageModule->factoryImage($mURLs, $mImgMod.$mAlign);
        $mod = new TexyModifier($mMod);
        $mContent = ltrim($mContent);
        if ($mLink) {
            if ($mLink === ':') {
                $link = new TexyLink($image->linkedURL === NULL ? $image->URL : $image->linkedURL);
                $link->raw = ':';
                $link->type = TexyLink::IMAGE;
            } else {
                $link = $tx->linkModule->factoryLink($mLink, NULL, NULL);
            }
        } else $link = NULL;
        if (is_callable(array($tx->handler, 'figure'))) {
            $res = $tx->handler->figure($parser, $image, $link, $mContent, $mod);
            if ($res !== Texy::PROCEED) return $res;
        }
        return $this->solve($image, $link, $mContent, $mod);
    }
    public function solve(TexyImage $image, $link, $content, $mod)
    {
        $tx = $this->texy;
        $hAlign = $image->modifier->hAlign;
        $mod->hAlign = $image->modifier->hAlign = NULL;
        $elImg = $tx->imageModule->solve($image, $link); // returns TexyHtml or false!
        if (!$elImg) return FALSE;
        $el = TexyHtml::el('div');
        if (!empty($image->width)) $el->attrs['style']['width'] = ($image->width + $this->widthDelta) . 'px';
        $mod->decorate($tx, $el);
        $el->children['img'] = $elImg;
        $el->children['caption'] = TexyHtml::el('p');
        $el->children['caption']->parseLine($tx, ltrim($content));
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
        } elseif ($this->class)
            $el->attrs['class'][] = $this->class;
        return $el;
    }
}
