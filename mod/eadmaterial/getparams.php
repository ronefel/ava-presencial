<?php
    defined('EADMATERIAL_INCLUDE_TEST') OR die('not allowed');

    $currenttab  = optional_param('tab',EADMATERIAL_TAB1,PARAM_INT);
    $currentpage = optional_param('pag',1              ,PARAM_INT);
	switch ($currenttab) {
	case EADMATERIAL_TAB1:
	case EADMATERIAL_TAB2:
		switch ($currentpage) {
		case EADMATERIAL_TAB2_PAGE1:
			$currentpagename = EADMATERIAL_TAB2_PAGE1NAME;
			break;
		case EADMATERIAL_TAB2_PAGE2:
			$currentpagename = EADMATERIAL_TAB2_PAGE2NAME;
			break;
		case EADMATERIAL_TAB2_PAGE3:
			$currentpagename = EADMATERIAL_TAB2_PAGE3NAME;
			break;
		case EADMATERIAL_TAB2_PAGE4:
			$currentpagename = EADMATERIAL_TAB2_PAGE4NAME;
			break;
		case EADMATERIAL_TAB2_PAGE5:
			$currentpagename = EADMATERIAL_TAB2_PAGE5NAME;
			break;
		default:
			echo 'I am at the row '.__LINE__.' of the file '.__FILE__.'<br />';
			echo 'I have $currentpage = '.$currentpage.'<br />';
			echo 'But the right "case" is missing<br />';
		}
		break;
	case EADMATERIAL_TAB3:
		switch ($currentpage) {
		case EADMATERIAL_TAB3_PAGE1:
			$currentpagename = EADMATERIAL_TAB3_PAGE1NAME;
			break;
		case EADMATERIAL_TAB3_PAGE2:
			$currentpagename = EADMATERIAL_TAB3_PAGE2NAME;
			break;
		default:
			echo 'I am at the row '.__LINE__.' of the file '.__FILE__.'<br />';
			echo 'I have $currentpage = '.$currentpage.'<br />';
			echo 'But the right "case" is missing<br />';
		}
		break;
	default:
		echo 'I am at the row '.__LINE__.' of the file '.__FILE__.'<br />';
		echo 'I have $currenttab = '.$currenttab.'<br />';
		echo 'But the right "case" is missing<br />';
	}
    
?>