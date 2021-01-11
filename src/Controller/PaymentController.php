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
     * @Route("/subscription", name="subscription", methods={"GET"})
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('payment/index.html.twig');
    }

    /**
     * @Route("/payment/process", name="payment_process", methods={"POST"})
     *
     * @return Response
     */
    public function paymentProcess(): Response
    {
        $stripe = new \Stripe\StripeClient(
            'sk_test_51I7qCgIjktDIYiezUfNYo411jpXTPey9JPxQBzojqxMJxHKmUA6XN2czkq5r4dGieTTSZytFtYosvhLReG1m3z3E00GDzfPTIn'
          );

        $date = new \DateTime();
        $period = intval($_POST['subscription']);
        $email = $_POST['email'];

        $customer = $stripe->customers->create([
            'description' => 'New Customer (created for API docs)',
            'email' => $email,
        ]);
        
        try {
            $payment = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                  'number' => intval($_POST['card_number']),
                  'exp_month' => intval($_POST['card_exp_month']),
                  'exp_year' => intval($_POST['card_exp_year']),
                  'cvc' => $_POST['card_cvc'],
                ],
            ]);
        } catch (\Stripe\Exception\CardException $e) {
            $this->addflash('error', $e->getMessage());
            return $this->redirectToRoute('subscription');
        }

        $stripe->paymentMethods->attach(
            $payment->id,
            ['customer' => $customer->id]
        );

        $updatedCustomer = $stripe->customers->update(
            $customer->id,
            ['invoice_settings' => ['default_payment_method' => $payment->id]]
          );
        
        if ($period === 6) {
            $subscription = $stripe->subscriptions->create([
                'customer' => $updatedCustomer->id,
                'cancel_at' => $date->modify('+6 month')->getTimestamp(),
                'items' => [
                    ['price' => 'price_1I7sM3IjktDIYiezn6kNu6sh'],
                ],
            ]);
        } elseif ($period === 12) {
            $subscription = $stripe->subscriptions->create([
                'customer' => $updatedCustomer->id,
                'cancel_at' => $date->modify('+12 month')->getTimestamp(),
                'items' => [
                    ['price' => 'price_1I7sUSIjktDIYiez9FXqCMHS'],
                ],
            ]);
        } else {
            $subscription = $stripe->subscriptions->create([
                'customer' => $updatedCustomer->id,
                'cancel_at' => $date->modify('+24 month')->getTimestamp(),
                'items' => [
                    ['price' => 'price_1I7sVlIjktDIYiezv2ni8asi'],
                ],
            ]);
        }
        

        $this->addflash('success', 'Subsciption activated');

        return $this->render('subscription/index.html.twig');
    }
}
