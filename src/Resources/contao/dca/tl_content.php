<?php

/**
 * Dynamically add the parent table
 */
if (Input::get('do') == 'portfolio')
{
    $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_portfolio';
    $GLOBALS['TL_DCA']['tl_content']['fields']['type']['options_callback'] = array('tl_content_portfolio', 'getContentElements');
}

/**
 * Class tl_content_portfolio
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_content_portfolio extends tl_content
{
    /**
     * Return all portfolio content elements as array
     * @return array
     */
    public function getContentElements()
    {
        $arrGroups = array();
        $arrAllowedElements = array('headline', 'text', 'image', 'gallery', 'dma_eg_3', 'dma_eg_6', 'dma_eg_5');

        foreach (parent::getContentElements() as $strGroup => $arrElements)
        {
            foreach (array_values($arrElements) as $strElement)
            {
                if (in_array($strElement, $arrAllowedElements))
                {
                    $arrGroups[$strGroup][] = $strElement;
                }
            }
        }

        return $arrGroups;
    }
}
