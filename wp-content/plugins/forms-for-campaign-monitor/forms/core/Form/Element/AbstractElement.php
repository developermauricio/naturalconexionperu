<?php

namespace forms\core\form\element;



abstract class AbstractElement
{
    /**
     * @var string|int
     */
    protected $_id;

    /**
     * @var string
     */
    protected $_type;

    /**
     * @var Form
     */
    protected $_form;

    /**
     * @var array
     */
    protected $_elements;

    /**
     * @var RendererInterface
     */
    protected $_renderer;


    /**
     * @var Escaper
     */
    protected $_escaper;

    /**
     * Lock html attribute
     *
     * @var string
     */
    private $lockHtmlAttribute = 'data-locked';


    public function __construct() {

        $this->_renderer = \Magento\Framework\Data\Form::getElementRenderer();
    }

    /**
     * Add form element
     *
     * @param AbstractElement $element
     * @param bool $after
     * @return Form
     */
    public function addElement(AbstractElement $element, $after = false)
    {
        if ($this->getForm()) {
            $this->getForm()->checkElementId($element->getId());
            $this->getForm()->addElementToCollection($element);
        }

        // Commenting the following line as "parent keyword without parent class" is deprecated in PHP 7.4
        // This method doesn't seem to be used anyway and currently throwing an error due to wordpress custom hook script
        // parent::addElement($element, $after);
        return $this;
    }




    /**
     * Get id.
     *
     * @return string|int
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Get form
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->_form;
    }

    /**
     * Set the Id.
     *
     * @param string|int $id
     * @return $this
     */
    public function setId($id)
    {
        $this->_id = $id;
        return $this;
    }

    /**
     * Get the Html Id.
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getForm()->getHtmlIdPrefix() . $this->getData('html_id') . $this->getForm()->getHtmlIdSuffix();
    }

    /**
     * Get the name.
     *
     * @return mixed
     */
    public function getName()
    {
        $name = $this->getData('name');
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    /**
     * Set the type.
     *
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->_type = $type;
        $this->setData('type', $type);
        return $this;
    }

    /**
     * @param AbstractForm $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->_form = $form;
        return $this;
    }

    /**
     * Remove field
     *
     * @param string $elementId
     * @return AbstractForm
     */
    public function removeField($elementId)
    {
        $this->getForm()->removeField($elementId);

        // Commenting the following line as "parent keyword without parent class" is deprecated in PHP 7.4
        // This method doesn't seem to be used anyway and currently throwing an error due to wordpress custom hook script
        // return parent::removeField($elementId);
    }

    /**
     * Return the attributes for Html.
     *
     * @return string[]
     */
    public function getHtmlAttributes()
    {
        return [
            'type',
            'title',
            'class',
            'style',
            'onclick',
            'onchange',
            'disabled',
            'readonly',
            'tabindex',
            'placeholder',
            'data-form-part',
            'data-role',
            'data-action',
            'checked',
        ];
    }

    /**
     * Add a class.
     *
     * @param string $class
     * @return $this
     */
    public function addClass($class)
    {
        $oldClass = $this->getClass();
        $this->setClass($oldClass . ' ' . $class);
        return $this;
    }

    /**
     * Remove CSS class
     *
     * @param string $class
     * @return $this
     */
    public function removeClass($class)
    {
        $classes = array_unique(explode(' ', $this->getClass()));
        if (false !== ($key = array_search($class, $classes))) {
            unset($classes[$key]);
        }
        $this->setClass(implode(' ', $classes));
        return $this;
    }

    /**
     * Escape a string's contents.
     *
     * @param string $string
     * @return string
     */
    protected function _escape($string)
    {
        return htmlspecialchars($string, ENT_COMPAT);
    }

    /**
     * Return the escaped value of the element specified by the given index.
     *
     * @param null|int|string $index
     * @return string
     */
    public function getEscapedValue($index = null)
    {
        $value = $this->getValue($index);

        if ($filter = $this->getValueFilter()) {
            $value = $filter->filter($value);
        }
        return $this->_escape($value);
    }

    /**
     * Set the renderer.
     *
     * @param RendererInterface $renderer
     * @return $this
     */
    public function setRenderer(RendererInterface $renderer)
    {
        $this->_renderer = $renderer;
        return $this;
    }

    /**
     * Get the renderer.
     *
     * @return RendererInterface
     */
    public function getRenderer()
    {
        return $this->_renderer;
    }

    /**
     * @param null|string $suffix
     * @return string
     */
    protected function _getUiId($suffix = null)
    {
        if ($this->_renderer instanceof \Magento\Framework\View\Element\AbstractBlock) {
            return $this->_renderer->getUiId($this->getType(), $this->getName(), $suffix);
        } else {
            return ' data-ui-id="form-element-' . $this->getName() . ($suffix ?: '') . '"';
        }
    }

    /**
     * Get the Html for the element.
     *
     * @return string
     */
    public function getElementHtml()
    {
        $html = '';
        $htmlId = $this->getHtmlId();

        $beforeElementHtml = $this->getBeforeElementHtml();
        if ($beforeElementHtml) {
            $html .= '<label class="addbefore" for="' . $htmlId . '">' . $beforeElementHtml . '</label>';
        }

        $html .= '<input id="' . $htmlId . '" name="' . $this->getName() . '" ' . $this->_getUiId() . ' value="' .
            $this->getEscapedValue() . '" ' . $this->serialize($this->getHtmlAttributes()) . '/>';

        $afterElementJs = $this->getAfterElementJs();
        if ($afterElementJs) {
            $html .= $afterElementJs;
        }

        $afterElementHtml = $this->getAfterElementHtml();
        if ($afterElementHtml) {
            $html .= '<label class="addafter" for="' . $htmlId . '">' . $afterElementHtml . '</label>';
        }

        return $html;
    }

    /**
     * Get the before element html.
     *
     * @return mixed
     */
    public function getBeforeElementHtml()
    {
        return $this->getData('before_element_html');
    }

    /**
     * Get the after element html.
     *
     * @return mixed
     */
    public function getAfterElementHtml()
    {
        return $this->getData('after_element_html');
    }

    /**
     * Get the after element Javascript.
     *
     * @return mixed
     */
    public function getAfterElementJs()
    {
        return $this->getData('after_element_js');
    }

    /**
     * Render HTML for element's label
     *
     * @param string $idSuffix
     * @param string $scopeLabel
     * @return string
     */
    public function getLabelHtml($idSuffix = '', $scopeLabel = '')
    {
        $scopeLabel = $scopeLabel ? ' data-config-scope="' . $scopeLabel . '"' : '';

        if ($this->getLabel() !== null) {
            $html = '<label class="label admin__field-label" for="' .
                $this->getHtmlId() . $idSuffix . '"' . $this->_getUiId(
                    'label'
                ) . '><span' . $scopeLabel . '>' . $this->_escape(
                    $this->getLabel()
                ) . '</span></label>' . "\n";
        } else {
            $html = '';
        }
        return $html;
    }

    /**
     * Get the default html.
     *
     * @return mixed
     */
    public function getDefaultHtml()
    {
        $html = $this->getData('default_html');
        if ($html === null) {
            $html = $this->getNoSpan() === true ? '' : '<div class="admin__field">' . "\n";
            $html .= $this->getLabelHtml();
            $html .= $this->getElementHtml();
            $html .= $this->getNoSpan() === true ? '' : '</div>' . "\n";
        }
        return $html;
    }

    /**
     * Get the html.
     *
     * @return mixed
     */
    public function getHtml()
    {
        if ($this->getRequired()) {
            $this->addClass('required-entry _required');
        }
        if ($this->_renderer) {
            $html = $this->_renderer->render($this);
        } else {
            $html = $this->getDefaultHtml();
        }
        return $html;
    }

    /**
     * Get the html.
     *
     * @return mixed
     */
    public function toHtml()
    {
        return $this->getHtml();
    }

    /**
     * Serialize the element.
     *
     * @param string[] $attributes
     * @param string $valueSeparator
     * @param string $fieldSeparator
     * @param string $quote
     * @return string
     */
    public function serialize($attributes = [], $valueSeparator = '=', $fieldSeparator = ' ', $quote = '"')
    {
        if ($this->isLocked() && !empty($attributes)) {
            $attributes[] = $this->lockHtmlAttribute;
        }
        if (in_array('disabled', $attributes) && !empty($this->_data['disabled'])) {
            $this->_data['disabled'] = 'disabled';
        } else {
            unset($this->_data['disabled']);
        }
        if (in_array('checked', $attributes) && !empty($this->_data['checked'])) {
            $this->_data['checked'] = 'checked';
        } else {
            unset($this->_data['checked']);
        }

        // Commenting the following line as "parent keyword without parent class" is deprecated in PHP 7.4
        // This method doesn't seem to be used anyway and currently throwing an error due to wordpress custom hook script
        // return parent::serialize($attributes, $valueSeparator, $fieldSeparator, $quote);
    }

    /**
     * Indicates the elements readonly status.
     *
     * @return mixed
     */
    public function getReadonly()
    {
        if ($this->hasData('readonly_disabled')) {
            return $this->_getData('readonly_disabled');
        }

        return $this->_getData('readonly');
    }

    /**
     * Get the container Id.
     *
     * @return mixed
     */
    public function getHtmlContainerId()
    {
        if ($this->hasData('container_id')) {
            return $this->getData('container_id');
        } elseif ($idPrefix = $this->getForm()->getFieldContainerIdPrefix()) {
            return $idPrefix . $this->getId();
        }
        return '';
    }

    /**
     * Add specified values to element values
     *
     * @param string|int|array $values
     * @param bool $overwrite
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function addElementValues($values, $overwrite = false)
    {
        if (empty($values) || is_string($values) && trim($values) == '') {
            return $this;
        }
        if (!is_array($values)) {
            $values = $this->_escaper->escapeHtml(trim($values));
            $values = [$values => $values];
        }
        $elementValues = $this->getValues();
        if (!empty($elementValues)) {
            foreach ($values as $key => $value) {
                if (isset($elementValues[$key]) && $overwrite || !isset($elementValues[$key])) {
                    $elementValues[$key] = $this->_escaper->escapeHtml($value);
                }
            }
            $values = $elementValues;
        }
        $this->setValues($values);

        return $this;
    }

    /**
     * Lock element
     *
     * @return void
     */
    public function lock()
    {
        $this->setData($this->lockHtmlAttribute, 1);
    }

    /**
     * Is element locked
     *
     * @return bool
     */
    public function isLocked()
    {
        return $this->getData($this->lockHtmlAttribute) == 1;
    }
}
