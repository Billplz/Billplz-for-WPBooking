<?php

class WPBooking_Billplz_Gateway extends WPBooking_Abstract_Payment_Gateway
{
    static $_inst = false;
    protected $gateway_id = 'billplz';
            //settings fields
    protected $settings = [];

    protected $gatewayObject = false;
    public function __construct()
    {
        $this->gateway_info  = [
            'label' => esc_html__("Billplz", 'bwp')
        ];

        $this->settings = array(
        array(
            'id' => 'enable',
            'label' => esc_html__("Billplz", 'bwp'),
            'type'           => 'checkbox',
            'std'            => '',
            'checkbox_label' => esc_html__("Yes, I want to enable Billplz", 'bwp')
        ),
        array(
            'id'    => 'title',
            'label' => esc_html__('Title', 'bwp'),
            'type'  => 'text',
            'std'   => 'Billplz',
        ),
        array(
            'id'    => 'desc',
            'label' => esc_html__('Descriptions', 'bwp'),
            'type'  => 'textarea',
            'std'   => 'Billplz Payment Gateway'
        ),
        array(
            'type' => 'hr'
        ),
        array(
            'id'    => 'api_key',
            'label' => esc_html__('API Secret Key', 'bwp'),
            'type'  => 'text',
            'std'   => '',
        ),
        array(
            'id'    => 'collection_id',
            'label' => esc_html__('Collection ID', 'bwp'),
            'type'  => 'text',
            'std'   => '',
        ),
        array(
            'id'    => 'x_signature_key',
            'label' => esc_html__('X Signature Key', 'bwp'),
            'type'  => 'text',
            'std'   => '',
        ),
        array(
            'id'    => 'reference_1_label',
            'label' => esc_html__('Reference 1 Label', 'bwp'),
            'type'  => 'text',
            'std'   => '',
        ),
        array(
            'id'    => 'reference_1',
            'label' => esc_html__('Reference 1', 'bwp'),
            'type'  => 'text',
            'std'   => '',
        )
        );
        parent::__construct();
    }

    function do_checkout($order_id)
    {
        $order = new WB_Order($order_id);
        $connect = new BillplzWPBookingWPConnect($this->get_option('api_key'));
        $connect->detectMode();
        $billplz = new BillplzWPBookingAPI($connect);

        $parameter = array(
            'collection_id' => $this->get_option('collection_id'),
            'email' => trim($order->get_customer_email()),
            'mobile'=> trim($order->get_customer('phone')),
            'name' => mb_substr($order->get_customer('full_name'), 0, 255),
            'amount' => strval($order->get_total() * 100),
            'callback_url' => $this->get_return_url($order_id),
            'description' => mb_substr('New Order In '.$order->get_booking_date(), 0, 200)
        );
        $optional = array(
            'redirect_url' => $this->get_return_url($order_id),
            'reference_1_label' => mb_substr(trim($this->get_option('reference_1_label')), 0, 20),
            'reference_1' => mb_substr(trim($this->get_option('reference_1')), 0, 120),
            'reference_2_label' => 'Order ID',
            'reference_2' => $order->get_order_id()
        );
        list($rheader, $rbody) = $billplz->toArray($billplz->createBill($parameter, $optional, '0'));
        
        if ($rheader !== 200) {
            return ['status' => 0];
        }
        return ['status' => 1, 'redirect' => $rbody['url']];
    }

    public function complete_purchase($order_id)
    {
        try {
            $data = BillplzWPBookingWPConnect::getXSignature($this->get_option('x_signature_key'));
        } catch (Exception $e) {
            exit($e->getMessage());
        }
        $connect = new BillplzWPBookingWPConnect($this->get_option('api_key'));
        $connect->detectMode();
        $billplz = new BillplzWPBookingAPI($connect);
        list($rheader, $rbody) = $billplz->toArray($billplz->getBill($data['id']));

        if ($rheader !== 200) {
            exit(print_r($rbody, true));
        }

        if ($rbody['paid']) {
            return true;
        } else {
            return false;
        }
    }

    static function inst()
    {
        if (!self::$_inst) {
            self::$_inst = new self();
        }

            return self::$_inst;
    }
}

WPBooking_Billplz_Gateway::inst();
