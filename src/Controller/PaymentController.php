<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Order\Order;

class PaymentController extends AbstractController
{
    /**
     * @Route("/payment", name="payment", methods={"GET"})
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('payment/index.html.twig');
    }

    /**
     * @Route("/payment/process", name="payment_process", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function paymentProcess(): Response
    {
        dd($_GET, $_POST);

        $stripe = new \Stripe\StripeClient(
            'sk_test_51I7qCgIjktDIYiezUfNYo411jpXTPey9JPxQBzojqxMJxHKmUA6XN2czkq5r4dGieTTSZytFtYosvhLReG1m3z3E00GDzfPTIn'
          );

        $period = $_GET['period'];
        $email = $_POST['email'];

        $customer = $stripe->customers->create([
            'description' => 'New Customer (created for API docs)',
            'email' => $email,
        ]);
        
        $payment = $stripe->paymentMethods->create([
            'type' => 'card',
            'card' => [
              'number' => '4242424242424242',
              'exp_month' => 1,
              'exp_year' => 2022,
              'cvc' => '314',
            ],
        ]);

        $stripe->paymentMethods->attach(
            $payment->id,
            ['customer' => $customer->id]
        );

        $updatedCustomer = $stripe->customers->update(
            $customer->id,
            ['invoice_settings' => ['default_payment_method' => $payment->id]]
          );
        
        if (intval($period) === 6) {
            $subscription = $stripe->subscriptions->create([
                'customer' => $updatedCustomer->id,
                'items' => [
                    ['price' => 'price_1I7sM3IjktDIYiezn6kNu6sh'],
                ],
            ]);
        } elseif (intval($period) === 12) {
            $subscription = $stripe->subscriptions->create([
                'customer' => $updatedCustomer->id,
                'items' => [
                    ['price' => 'price_1I7sUSIjktDIYiez9FXqCMHS'],
                ],
            ]);
        } else {
            $subscription = $stripe->subscriptions->create([
                'customer' => $updatedCustomer->id,
                'items' => [
                    ['price' => 'price_1I7sVlIjktDIYiezv2ni8asi'],
                ],
            ]);
        }
        

        $this->addflash('success', 'Subsciption activated');

        return $this->render('payment/index.html.twig', [
            'email' => $email,
        ]);
    }
}
