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
     * @Route("/payment/{email}/{total}", name="payment", methods={"GET"})
     *
     * @param string $email
     * @param integer $total
     * @return Response
     */
    public function index(string $email, int $total): Response
    {
        return $this->render('payment/index.html.twig', [
            'email' => $email,
            'total' => $total
        ]);
    }

    /**
     * @Route("/payment/process", name="payment_process", methods={"POST"})
     *
     * @return Response
     */
    public function process(): Response
    {
        require_once('vendor/autoload.php');

        $stripe = [
            "secretkey" => "sk_test_51I7qCgIjktDIYiezUfNYo411jpXTPey9JPxQBzojqxMJxHKmUA6XN2czkq5r4dGieTTSZytFtYosvhLReG1m3z3E00GDzfPTIn",
            "publishable_key" => "pk_test_51I7qCgIjktDIYiezyk245q27khXTpXCzXgu9AMx3A6n1ay8U81Ap7Rt8EpMUwu9kk9qWC2QC5Ymi0MJ9eJahAiBR00gqYitQ6F"
        ];

        \Stripe\Stripe::setApikey($stripe['secret_key']);

        $period = $_POST['period'];
        $token = $_POST['stripetoken'];
        $email = $_POST['stripeEmail'];

        $customer = \Stripe\Customer::create([
            'email' => $email,
            'source' => $token,
        ]);

        \Stripe\Subscription::create([
            "customer" => $customer->id,
            "items" => [
                "plan" => "price_1I7sM3IjktDIYiezn6kNu6sh"
            ],
        ]);

        return $this->render('payment/index.html.twig', [
            
        ]);
    }
}
