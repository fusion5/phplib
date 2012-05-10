<?php
if (!class_exists('Texy', FALSE)) die();
class TexyHorizLineModule extends TexyModule
{
    protected $default = array('horizline' => TRUE);
    public function begin()
    {
        $this->texy->registerBlockPattern(
            array($this, 'pattern'),
            '#^(?:\*{3,}|-{3,})\ *'.TEXY_MODIFIER.'?()$#mU',
            'horizline'
        );
    }
    public function pattern($parser, $matches)
    {
        list(, $mMod) = $matches;
        $tx = $this->texy;
        $el = TexyHtml::el('hr');
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $el);
        if (is_callable(array($tx->handler, 'afterHorizline')))
            $tx->handler->afterHorizline($parser, $el, $mod);
        return $el;
    }
} // TexyHorizlineModule
