<?php
/**
 * Solwin Infotech
 * Solwin Ournews Extension
 *
 * @category   Solwin
 * @package    Solwin_Ournews
 * @copyright  Copyright © 2006-2016 Solwin (https://www.solwininfotech.com)
 * @license    https://www.solwininfotech.com/magento-extension-license/
 */
?>
<?php

namespace Solwin\Ournews\Block\Adminhtml\News\Edit\Tab;

class News extends \Magento\Backend\Block\Widget\Form\Generic
implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * Wysiwyg config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * Status options
     *
     * @var \Solwin\Ournews\Model\News\Source\IsActive
     */
    protected $_isActiveOptions;

    /**
     * constructor
     *
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Solwin\Ournews\Model\News\Source\IsActive $isActiveOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Solwin\Ournews\Model\News\Source\IsActive $isActiveOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig   = $wysiwygConfig;
        $this->_isActiveOptions = $isActiveOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Solwin\Ournews\Model\News $news */
        $news = $this->_coreRegistry->registry('solwin_ournews_news');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('news_');
        $form->setFieldNameSuffix('news');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('News Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        $fieldset->addType('image',
                'Solwin\Ournews\Block\Adminhtml\News\Helper\Image');
        if ($news->getId()) {
            $fieldset->addField(
                'news_id',
                'hidden',
                ['name' => 'news_id']
            );
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name'  => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'url_key',
            'text',
            [
                'name'  => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
                'class' => 'validate-identifier',
                'note' => __('Empty to auto create url key'),
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore
                    ->getStoreValuesForForm(false, true),
                ]
            );
            $renderer = $this->getLayout()
                    ->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset'
                            . '\Element'
                    );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]',
                    'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $news->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'url',
            'text',
            [
                'name'  => 'url',
                'label' => __('URL'),
                'class' => 'validate-url',
                'title' => __('URL'),
                'note' => __('Add URL in your news which will display after description in view page.'),
            ]
        );
        $fieldset->addField(
            'image',
            'image',
            [
                'name'  => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
            ]
        )->setAfterElementHtml('<p class="image-note">Allowed Image Extensions are jpg,jpeg,png,gif.</p>
          <style>
          #news_image_image {
              width: 200px;
              height: auto;
          }
          #news_image {
              display: block;
          }
          #news_image_delete {
              vertical-align: middle;
          }
          </style>
          <script type="text/javascript">
          require(["jquery", "mage/mage"], function(jQuery){
              (function ($) {
                  $(":input[name=image]").change(function () {
                      var obj = $(this);
                      var fileExtension = ["jpg","jpeg","png","gif"];
                      if ($.inArray(obj.val().split(".").pop().toLowerCase(), fileExtension) == -1) {
                          $(".image-note").css("color","#eb5202");
                          obj.val("");
                      } else {
                        $(".image-note").css("color","");
                      }
                  });
              })(jQuery);
          });
          </script>');

        $fieldset->addField(
            'start_publish_date',
            'date',
            [
                'name'  => 'start_publish_date',
                'label' => __('Start Publish Date'),
                'title' => __('Start Publish Date'),
                'required' => true,
                'date_format' => $this->_localeDate->getDateFormat(
                    \IntlDateFormatter::SHORT
                ),
                'class' => 'validate-date',
            ]
        )->setAfterElementHtml('
        <p class="startdate-note" style="display:none;">Start date must be less then of end date.</p>
        <script type="text/javascript">
        require(["jquery", "mage/mage"], function(jQuery){
            (function ($) {
                $(":input[id=news_start_publish_date]").change(function () {
                    var startDate = $("#news_start_publish_date").val();
                    var endDate = $("#news_end_publish_date").val();
                    var obj = $(this);
                    if(new Date(startDate) >= new Date(endDate)) {
                        $(".startdate-note").show();
                        $(".startdate-note").css("color","#f61616");
                        obj.val("");
                        obj.focus();
                    } else {
                        $(".startdate-note").hide();
                        $(".startdate-note").css("color","");
                    }
                });
            })(jQuery);
        });
        </script>
        ');

        $fieldset->addField(
            'end_publish_date',
            'date',
            [
                'name'  => 'end_publish_date',
                'label' => __('End Publish Date'),
                'title' => __('End Publish Date'),
                'required' => true,
                'date_format' => $this->_localeDate->getDateFormat(
                    \IntlDateFormatter::SHORT
                ),
                'class' => 'validate-date',
            ]
        )->setAfterElementHtml('
        <p class="enddate-note" style="display:none;">End date must be greater than of start date.</p>
                        <script type="text/javascript">
                        require(["jquery", "mage/mage"], function(jQuery){
                            (function ($) {
                              $(":input[id=news_end_publish_date]").change(function () {
                                  var startDate = $("#news_start_publish_date").val();
                                  var endDate = $("#news_end_publish_date").val();
                                  var obj = $(this);
                                 if(new Date(startDate) >= new Date(endDate)) {
                                    $(".enddate-note").show();
                                    $(".enddate-note").css("color","#f61616");
                                    obj.val("");
                                    obj.focus();
                                  } else {
                                    $(".enddate-note").hide();
                                    $(".enddate-note").css("color","");
                                  }
                              });
                            })(jQuery);
                        });
                        </script>
        ');

        $fieldset->addField(
            'is_active',
            'select',
            [
                'name'  => 'is_active',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values' => array_merge(['' => ''],
                        $this->_isActiveOptions->toOptionArray()),
            ]
        );


        $newsData = $this->_session->getData('solwin_ournews_news_data', true);
        if ($newsData) {
            $news->addData($newsData);
        } else {
            if (!$news->getId()) {
                $news->addData($news->getDefaultValues());
            }
        }
        $form->addValues($news->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('News');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}