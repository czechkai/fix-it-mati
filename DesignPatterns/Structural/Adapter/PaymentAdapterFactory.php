<?php
/**
 * Adapter Pattern - Payment Gateway Factory
 * 
 * Creates appropriate payment adapter based on gateway type
 */

namespace FixItMati\DesignPatterns\Structural\Adapter;

class PaymentAdapterFactory
{
    /**
     * Create payment gateway adapter
     * 
     * @param string $gateway Gateway type: 'stripe', 'paypal', 'gcash'
     * @param array $config Optional configuration for the gateway
     * @return PaymentGatewayInterface
     * @throws \InvalidArgumentException
     */
    public static function createGateway(string $gateway, array $config = []): PaymentGatewayInterface
    {
        $gateway = strtolower($gateway);
        
        switch ($gateway) {
            case 'stripe':
                return new StripePaymentAdapter($config);
                
            case 'paypal':
                return new PayPalPaymentAdapter($config);
                
            case 'gcash':
                return new GCashPaymentAdapter($config);
                
            default:
                throw new \InvalidArgumentException("Unsupported payment gateway: {$gateway}");
        }
    }
    
    /**
     * Get list of supported gateways
     * 
     * @return array
     */
    public static function getSupportedGateways(): array
    {
        return ['stripe', 'paypal', 'gcash'];
    }
    
    /**
     * Check if gateway is supported
     * 
     * @param string $gateway
     * @return bool
     */
    public static function isGatewaySupported(string $gateway): bool
    {
        return in_array(strtolower($gateway), self::getSupportedGateways());
    }
}
