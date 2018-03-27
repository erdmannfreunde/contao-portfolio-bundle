<?php

/**
 * Dynamically add the parent table
 */
if (Input::get('do') == 'portfolio')
{
    $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'tl_portfolio';
}
