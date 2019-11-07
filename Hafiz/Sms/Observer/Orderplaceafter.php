<?php
namespace Hafiz\Sms\Observer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
class Orderplaceafter implements ObserverInterface
//class ProcessGatewayRedirect implements ObserverInterface
{
    private $storeManager;
    protected $_checkoutSession;
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
    	try{
		    	$orderId = $observer->getEvent()->getOrderIds();
		        $base_url = $this->storeManager->getStore()->getBaseUrl();
		        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		        $order = $objectManager->create('\Magento\Sales\Model\Order') ->load($orderId[0]);
		        $payment = $order->getPayment();
		        $method = $payment->getMethodInstance();
		        $methodTitle = $method->getincrement_id();
		        $number = $order->getShippingAddress()->getTelephone();
		        $str = ltrim($number, '0');
		        $number1 ='92'.$str;
		        $order_data= $order->getData();
		        $total =floor ($order_data['base_grand_total']);
		        $ordernumber = $order_data['increment_id'];
		        //getting session key
		        $planetbeyondApiUrl="https://telenorcsms.com.pk:27677/corporate_sms2/api/auth.jsp?msisdn=#username#&password=#password#";
            //use username and password
		        $userName="username";
		        $password="password";
		        $url = str_replace("#username#",$userName,$planetbeyondApiUrl);
		        $url = str_replace("#password#",$password,$url);
		        $url = urldecode($url);

		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_URL, $url);
		        curl_setopt($ch, CURLOPT_HEADER, 0);
		        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
		        $response = curl_exec($ch);


		         $response = file_get_contents($url);

		        $response1 = simplexml_load_string($response);

		        $sessionKey = isset($response1->data) ?  $response1->data : '';
		        $messageText ="Thank you for Your Order at RCG Your Order #" . $ordernumber .
		         " Total Bill Rs:" . $total .
		         " You can check the status of your Order by logging into your Account";
		        //echo $messageText;
		        //for quick mesage send
		        $planetbeyondApiSendSmsUrl="https://telenorcsms.com.pk:27677/corporate_sms2/api/sendsms.jsp?session_id=#session_id#&to=#to_number_csv#&text=#message_text#";
		        $url2 = str_replace("#message_text#",urlencode($messageText),$planetbeyondApiSendSmsUrl);
		        $url2 = str_replace("#to_number_csv#",$number1,$url2);
		        $urlWithSessionKey = str_replace("#session_id#",$sessionKey,$url2);
		        $urlWithSessionKey = $urlWithSessionKey . "&mask=" . 'RCG';
		        $url2 =$urlWithSessionKey;
		        $ch1 = curl_init();
		        curl_setopt($ch1, CURLOPT_URL, $url2);
		        curl_setopt($ch1, CURLOPT_HEADER, 0);
		        curl_setopt($ch1,CURLOPT_RETURNTRANSFER, true);
		        $response2 = curl_exec($ch1);
		        curl_close($ch1);
		        $status = $this->_checkoutSession->getLastOrderStatus();
		        return;

    		}catch(\Exception $e)
    		{
    			var_dump($e->getMessage());
    			die('excep');
    		}

}
}
