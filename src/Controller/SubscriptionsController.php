<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionsController extends AbstractController
{
    /**
     * @Route("/admin/subscriptions", name="subscriptions", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        $stripe = new \Stripe\StripeClient(
            getenv('API_KEY')
        );

        $subscriptions = [];

        $datas = $stripe->subscriptions->all();

        foreach ($datas->data as $subscription) {
            $subscriptions[] = [
                'id' => $subscription->id,
                'customer' => $stripe->customers->retrieve($subscription->customer, [])->email,
                'price' => $subscription->plan->amount,
                'ending' => (new \DateTime())->setTimestamp($subscription->cancel_at),
            ];
        }

        return $this->render('subscriptions/index.html.twig', [
            'subscriptions' => $subscriptions
        ]);
    }

    /**
     * @Route("/admin/subscriptions/action", name="subscription_actions", methods={"GET"})
     *
     * @return Response
     */
    public function actions(): Response
    {
        $stripe = new \Stripe\StripeClient(
            getenv('API_KEY')
        );

        $subscription = [];
        $prices = [];

        $datas = $stripe->subscriptions->retrieve(
            $_GET['id'],
            []
        );

        $dataPrices = $stripe->prices->all();

        $subscription = [
            'id' => $datas->id,
            'customer' => $stripe->customers->retrieve($datas->customer, [])->email,
            'price' => $datas->plan->amount,
            'ending' => (new \DateTime())->setTimestamp($datas->cancel_at),
        ];

        foreach ($dataPrices->data as $price) {
            $prices[] = [
                'id' => $price->id,
                'amount' => $price->unit_amount
            ];
        }

        return $this->render('subscriptions/actions.html.twig', [
            'subscription' => $subscription,
            'prices' => $prices
        ]);
    }

    /**
     * @Route("/admin/subscriptions/action/update-price", name="update_price", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function updtatePrice(): Response
    {
        $subscription = $_GET['id'];
        $price = $_POST['price'];

        $stripe = new \Stripe\StripeClient(
            getenv('API_KEY')
        );

        try {
            $stripe->subscriptions->update(
                $subscription,
                ['plan' => $price ]
            );
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('subscription_actions', ['id' => $subscription]);
        }
        
        $this->addFlash('success', 'Price updated');

        return $this->redirectToRoute('subscription_actions', ['id' => $subscription]);
    }

    /**
     * @Route("/admin/subscriptions/action/update-date", name="update_date", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function updateDate(): Response
    {
        $subscription = $_GET['id'];
        $date = (new \DateTime($_POST['endingDate']))->getTimestamp();

        $stripe = new \Stripe\StripeClient(
            getenv('API_KEY')
        );

        try {
            $stripe->subscriptions->update(
                $subscription,
                ['cancel_at' => $date ]
            );
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('subscription_actions', ['id' => $subscription]);
        }

        $this->addFlash('success', 'Date updated');

        return $this->redirectToRoute('subscription_actions', ['id' => $subscription]);
    }

    /**
     * @Route("/admin/subscriptions/action/relaunch", name="email_relaunch", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function relaunchEmail(): Response
    {
        $subscription = $_GET['id'];

        if ($_POST['email'] === "") {
            $this->addFlash('error', 'Please enter the customers email');
            return $this->redirectToRoute('subscription_actions', ['id' => $subscription]);
        } else {
            // SwiftMailer email treatment.
        }
        
        $this->addFlash('success', 'An email has been sent to the customer');

        return $this->redirectToRoute('subscription_actions', ['id' => $subscription]);
    }

    /**
     * @Route("/admin/subscriptions/action/refund", name="refund", methods={"GET", "POST"})
     *
     * @return Response
     */
    public function refund(): Response
    {
        $subscription = $_GET['id'];
        $customer = $_POST['customer'];
        $chargeToRefund = [];

        $stripe = new \Stripe\StripeClient(
            getenv('API_KEY')
        );

        $charges = $stripe->charges->all();

        foreach ($charges->data as $charge) {
            if ($stripe->customers->retrieve($charge->customer, [])->email === $customer) {
                $chargeToRefund = [
                    'id' => $charge->id,
                    'customer' => $stripe->customers->retrieve($charge->customer, [])->email
                ];

                $stripe->refunds->create([
                    'charge' => $chargeToRefund['id'],
                ]);

                $this->addFlash('success', 'The customer has been reimbursed');

                return $this->redirectToRoute('subscription_actions', ['id' => $subscription]);
            } 
        }
        
        $this->addFlash('erros', 'There is no payments for this customer');

        return $this->redirectToRoute('subscription_actions', ['id' => $subscription]);
    }
}
