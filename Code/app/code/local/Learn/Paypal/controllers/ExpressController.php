<?php

require_once(Mage::getModuleDir('controllers','Mage_Paypal').DS.'ExpressController.php');

class Learn_Paypal_ExpressController extends Mage_Paypal_ExpressController
{

    /**
     * Return checkout quote object
     *
     * @return Mage_Sales_Model_Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * PayPal session instance getter
     *
     * @return Mage_PayPal_Model_Session
     */
    private function _getSession()
    {
        return Mage::getSingleton('paypal/session');
    }

    /**
     * Submit the order
     */
    public function placeOrderAction()
    {
        try {
            $requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds();
            if ($requiredAgreements) {
                $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                if (array_diff($requiredAgreements, $postedAgreements)) {
                    Mage::throwException(Mage::helper('paypal')->__('Please agree to all the terms and conditions before placing the order.'));
                }
            }

            $this->_initCheckout();
            $this->_checkout->place($this->_initToken());

            // prepare session to success or cancellation page
            $session = $this->_getCheckoutSession();
            $session->clearHelperData();

            // "last successful quote"
            $quoteId = $this->_getQuote()->getId();
            $session->setLastQuoteId($quoteId)->setLastSuccessQuoteId($quoteId);

            // an order may be created
            $order = $this->_checkout->getOrder();
            if ($order) {
                $session->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
                // as well a billing agreement can be created
                $agreement = $this->_checkout->getBillingAgreement();
                if ($agreement) {
                    $session->setLastBillingAgreementId($agreement->getId());
                }
            }

            // recurring profiles may be created along with the order or without it
            $profiles = $this->_checkout->getRecurringPaymentProfiles();
            if ($profiles) {
                $ids = array();
                foreach($profiles as $profile) {
                    $ids[] = $profile->getId();
                }
                $session->setLastRecurringProfileIds($ids);
            }

            // redirect if PayPal specified some URL (for example, to Giropay bank)
            $url = $this->_checkout->getRedirectUrl();
            if ($url) {
                $this->getResponse()->setRedirect($url);
                return;
            }
            $this->_initToken(false); // no need in token anymore
            //$this->_redirect('checkout/onepage/success');

			/*- CUSTOM PAGE REDIRECTION -*/
            $this->_redirectUrl(Mage::getBaseUrl()."thank-you-for-shopping/");
			/*- CUSTOM PAGE REDIRECTION -*/

            return;
        } catch (Mage_Paypal_Model_Api_ProcessableException $e) {
            $this->_processPaypalApiError($e);
        } catch (Mage_Core_Exception $e) {
            Mage::helper('checkout')->sendPaymentFailedEmail($this->_getQuote(), $e->getMessage());
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/review');
        } catch (Exception $e) {
            Mage::helper('checkout')->sendPaymentFailedEmail(
                $this->_getQuote(),
                $this->__('Unable to place the order.')
            );
            $this->_getSession()->addError($this->__('Unable to place the order.'));
            Mage::logException($e);
            $this->_redirect('*/*/review');
        }
    }
}