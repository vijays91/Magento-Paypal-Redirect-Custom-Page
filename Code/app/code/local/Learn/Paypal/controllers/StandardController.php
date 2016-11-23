<?php

require_once(Mage::getModuleDir('controllers','Mage_Paypal').DS.'StandardController.php');

class Learn_Paypal_StandardController extends Mage_Paypal_StandardController
{
    public function  successAction()
    {
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($session->getPaypalStandardQuoteId(true));
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        //$this->_redirect('checkout/onepage/success', array('_secure'=>true));
		
		/*- CUSTOM PAGE REDIRECTION -*/
		$this->_redirectUrl(Mage::getBaseUrl()."thank-you-for-shopping/");
		/*- CUSTOM PAGE REDIRECTION -*/
    }
}
