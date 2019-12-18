<?php

namespace Chrometoaster\AdvancedTaxonomies\Extensions;

use Chrometoaster\AdvancedTaxonomies\Models\TaxonomyTerm;
use Chrometoaster\AdvancedTaxonomies\Validators\TaxonomyRulesValidator;
use SilverStripe\AssetAdmin\Forms\AssetFormFactory;
use SilverStripe\Assets\Folder;
use SilverStripe\Core\Extension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Tab;
use SilverStripe\Forms\TreeMultiselectField;

/**
 * Class FileFormFactoryTaxonomyExtension
 *
 * Adds TreeMultiselectField to asset admin to be able to tag files.
 */
class FileFormFactoryTaxonomyExtension extends Extension
{
    /**
     * Update/add fields to file admin form
     *
     * @param FieldList $fields
     * @param $controller
     * @param $formName
     * @param $context
     */
    public function updateFormFields(FieldList $fields, $controller, $formName, $context)
    {
        // Only add Tags field when this form is fileEditForm and appears in AssetAdmin interface
        // Note: the same form could used in fileInsertForm, fileSelectForm, etc. and the form type is align with
        // AssetFormFactory::TYPE_INSERT_LINK, AssetFormFactory::TYPE_INSERT_MEDIA, or AssetFormFactory::TYPE_SELECT
        $type = static::getFormTypeFromContext($context);

        if ($formName === 'fileEditForm' || $type === AssetFormFactory::TYPE_ADMIN) {
            $record = $context['Record'];

            if (!is_a($record, Folder::class)) {

                // Avoid using $fields->findOrMakeTab() so the tab "Tags" is added right after the first tab "Details"
                $editorTabSet = $fields->fieldByName('Editor');
                $tagsTab      = Tab::create('Tags', _t(self::class . '.TagsTabTitle', 'Tags'));
                $editorTabSet->insertAfter('Details', $tagsTab);

                $tags = TreeMultiselectField::create(
                    'Tags',
                    _t(self::class . '.Tags', 'Tags'),
                    TaxonomyTerm::class,
                    'ID',
                    'Name'
                );
                $tags->setTitleField('Name')
                    ->setEmptyString('Add tags by name')
                    ->setShowSearch(true);

                $fields->addFieldToTab(
                    'Editor.Tags',
                    $tags
                );
            }
        }
    }


    /**
     * Add custom validator to check required types and single select validation.
     *
     * @param $form
     * @param $controller
     * @param $name
     * @param $context
     */
    public function updateForm($form, $controller, $name, $context)
    {
        $defaultValidator = $form->getValidator();
        $validator        = TaxonomyRulesValidator::create();
        $validator->appendRequiredFields($defaultValidator);
        $validator->setHTMLOutput(false);

        $form->setValidator($validator);
    }


    /**
     * As getFormType() in FileFormFactory is protected method, we will replicate the function here
     *
     * @param $context
     * @return string
     */
    public static function getFormTypeFromContext($context)
    {
        return empty($context['Type']) ? AssetFormFactory::TYPE_ADMIN : $context['Type'];
    }
}
