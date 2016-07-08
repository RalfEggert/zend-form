<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ZendTest\Form\View\Helper;

use Zend\Form\Element;
use Zend\Form\View\Helper\FormElementErrors as FormElementErrorsHelper;

class FormElementErrorsTest extends CommonTestCase
{
    public function setUp()
    {
        $this->helper = new FormElementErrorsHelper();
        parent::setUp();
    }

    public function getMessageList()
    {
        return [
            'First error message',
            'Second error message',
            'Third error message',
        ];
    }

    public function testLackOfMessagesResultsInEmptyMarkup()
    {
        $element = new Element('foo');
        $markup  = $this->helper->render($element);
        $this->assertEquals('', $markup);
    }

    public function testRendersErrorMessagesUsingUnorderedListByDefault()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $this->helper->render($element);
        $this->assertRegexp('#<ul>\s*<li>First error message</li>\s*<li>Second error message</li>\s*<li>Third error message</li>\s*</ul>#s', $markup);
    }

    public function testRendersErrorMessagesUsingUnorderedListTranslated()
    {
        $mockTranslator = $this->getMock('Zend\I18n\Translator\Translator');
        $mockTranslator->expects($this->at(0))
            ->method('translate')
            ->will($this->returnValue('Translated first error message'));
        $mockTranslator->expects($this->at(1))
            ->method('translate')
            ->will($this->returnValue('Translated second error message'));
        $mockTranslator->expects($this->at(2))
            ->method('translate')
            ->will($this->returnValue('Translated third error message'));

        $this->helper->setTranslator($mockTranslator);
        $this->assertTrue($this->helper->hasTranslator());

        $this->helper->setTranslatorTextDomain('default');

        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $this->helper->render($element);
        $this->assertRegexp('#<ul>\s*<li>Translated first error message</li>\s*<li>Translated second error message</li>\s*<li>Translated third error message</li>\s*</ul>#s', $markup);
    }

    public function testCanSpecifyAttributesForOpeningTag()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $this->helper->render($element, ['class' => 'error']);
        $this->assertContains('ul class="error"', $markup);
    }

    public function testCanSpecifyAttributesForOpeningTagUsingInvoke()
    {
        $helper = $this->helper;
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $helper($element, ['class' => 'error']);
        $this->assertContains('ul class="error"', $markup);
    }

    public function testCanSpecifyAlternateMarkupStringsViaSetters()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $this->helper->setMessageOpenFormat('<div%s><span>')
                     ->setMessageCloseString('</span></div>')
                     ->setMessageSeparatorString('</span><span>')
                     ->setAttributes(['class' => 'error']);

        $markup = $this->helper->render($element);
        $this->assertRegexp('#<div class="error">\s*<span>First error message</span>\s*<span>Second error message</span>\s*<span>Third error message</span>\s*</div>#s', $markup);
    }

    public function testSpecifiedAttributesOverrideDefaults()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);
        $element->setAttributes(['class' => 'foo']);

        $markup = $this->helper->render($element, ['class' => 'error']);
        $this->assertContains('ul class="error"', $markup);
    }

    public function testGetAttributes()
    {
        $messages = $this->getMessageList();
        $element  = new Element('foo');
        $element->setMessages($messages);

        $this->helper->setAttributes(['class' => 'error']);

        $this->helper->render($element);

        $this->assertEquals(['class' => 'error'], $this->helper->getAttributes());
    }

    public function testRendersNestedMessageSetsAsAFlatList()
    {
        $messages = [
            [
                'First validator message',
            ],
            [
                'Second validator first message',
                'Second validator second message',
            ],
        ];
        $element  = new Element('foo');
        $element->setMessages($messages);

        $markup = $this->helper->render($element, ['class' => 'error']);
        $this->assertRegexp('#<ul class="error">\s*<li>First validator message</li>\s*<li>Second validator first message</li>\s*<li>Second validator second message</li>\s*</ul>#s', $markup);
    }

    public function testCallingTheHelperToRenderInvokeCanReturnObject()
    {
        $helper = $this->helper;
        $this->assertEquals($helper(), $helper);
    }
}
