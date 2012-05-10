<?php
if (!class_exists('Texy', FALSE)) die();
class TexyTableModule extends TexyModule
{
    protected $default = array('table' => TRUE);
    public $oddClass;
    public $evenClass;
    private $isHead;
    private $colModifier;
    private $last;
    private $row;
    public function begin()
    {
        $this->texy->registerBlockPattern(
            array($this, 'patternTable'),
            '#^(?:'.TEXY_MODIFIER_HV.'\n)?'   // .{color: red}
          . '\|.*()$#mU',                     // | ....
            'table'
        );
    }
    public function patternTable($parser, $matches)
    {
        list(, $mMod) = $matches;
        $tx = $this->texy;
        $el = TexyHtml::el('table');
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $el);
        $parser->moveBackward();
        if ($parser->next('#^\|(\#|\=){2,}(?!\\1)(.*)\\1*\|? *'.TEXY_MODIFIER_H.'?()$#Um', $matches)) {
            list(, , $mContent, $mMod) = $matches;
            $caption = $el->add('caption');
            $mod = new TexyModifier($mMod);
            $mod->decorate($tx, $caption);
            $caption->parseLine($tx, $mContent);
        }
        $this->isHead = FALSE;
        $this->colModifier = array();
        $this->last = array();
        $this->row = 0;
        while (TRUE) {
            if ($parser->next('#^\|[+-]{3,}$#Um', $matches)) {
                $this->isHead = !$this->isHead;
                continue;
            }
            if ($elRow = $this->patternRow($parser)) {
                $el->addChild($elRow);
                $this->row++;
                continue;
            }
            break;
        }
        if (is_callable(array($tx->handler, 'afterTable')))
            $tx->handler->afterTable($parser, $el, $mod);
        return $el;
    }
    protected function patternRow($parser)
    {
        $tx = $this->texy;
        $matches = NULL;
        if (!$parser->next('#^\|(.*)(?:|\|\ *'.TEXY_MODIFIER_HV.'?)()$#U', $matches))
            return FALSE;
        list(, $mContent, $mMod) = $matches;
        $elRow = TexyHtml::el('tr');
        $mod = new TexyModifier($mMod);
        $mod->decorate($tx, $elRow);
        if ($this->row % 2 === 0) {
            if ($this->oddClass) $elRow->attrs['class'][] = $this->oddClass;
        } else {
            if ($this->evenClass) $elRow->attrs['class'][] = $this->evenClass;
        }
        $col = 0;
        $elField = NULL;
        $mContent = str_replace('\\|', '&#x7C;', $mContent);
        foreach (explode('|', $mContent) as $field) {
            if (($field == '') && $elField) { // colspan
                $elField->colspan++;
                unset($this->last[$col]);
                $col++;
                continue;
            }
            $field = rtrim($field);
            if ($field === '^') { // rowspan
                if (isset($this->last[$col])) {
                    $this->last[$col]->rowspan++;
                    $col += $this->last[$col]->colspan;
                    continue;
                }
            }
            if (!preg_match('#(\*??)\ *'.TEXY_MODIFIER_HV.'??(.*)'.TEXY_MODIFIER_HV.'?()$#AU', $field, $matches)) continue;
            list(, $mHead, $mModCol, $mContent, $mMod) = $matches;
            if ($mModCol) {
                $this->colModifier[$col] = new TexyModifier($mModCol);
            }
            if (isset($this->colModifier[$col]))
                $mod = clone $this->colModifier[$col];
            else
                $mod = new TexyModifier;
            $mod->setProperties($mMod);
            $elField = new TexyTableFieldElement;
            $elField->setName($this->isHead || ($mHead === '*') ? 'th' : 'td');
            $mod->decorate($tx, $elField);
            $elField->parseLine($tx, $mContent);
            if ($elField->children === '') $elField->children  = "\xC2\xA0"; // &nbsp;
            $elRow->addChild($elField);
            $this->last[$col] = $elField;
            $col++;
        }
        return $elRow;
    }
} // TexyTableModule
class TexyTableFieldElement extends TexyHtml
{
    public $colspan = 1;
    public $rowspan = 1;
    public function startTag()
    {
        $this->attrs['colspan'] = $this->colspan < 2 ? NULL : $this->colspan;
        $this->attrs['rowspan'] = $this->rowspan < 2 ? NULL : $this->rowspan;
        return parent::startTag();
    }
} // TexyTableFieldElement
