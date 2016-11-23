<?php

require_once(Mage::getModuleDir('controllers','Mage_Paypal').DS.'HostedproController.php');

class Learn_Paypal_HostedproController extends Mage_Paypal_HostedproController
{

    public function returnAction() {
        $session = $this->_getCheckout();
        //TODO: some actions with order
        if ($session->getLastRealOrderId()) {
            // $this->_redirect('checkout/onepage/success');
			
			/*- CUSTOM PAGE REDIRECTION -*/
            $this->_redirectUrl(Mage::getBaseUrl()."thank-you-for-shopping/");
			/*- CUSTOM PAGE REDIRECTION -*/
        }
    }

}
