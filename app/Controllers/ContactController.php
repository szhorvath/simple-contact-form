<?php

namespace App\Controllers;

use PDO;
use Carbon\Carbon;
use App\Models\Lead;

class ContactController
{
    protected $c;

    protected $db;

    public function __construct($c)
    {
        $this->c = $c;
        $this->db = $c->db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function index($request, $response)
    {
        return $response->setBody($this->c->view->render('form.twig', [
            'csrf_token' => $this->c->session->token()
        ]))->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    public function store($request, $response)
    {
        $data = $request->getParsedBody();

        $validation = $this->c->validator->check($data, [
            'csrf_token' => [
                'required'  => true,
                'csrf' => true,
            ],
            'name' => [
                'required'  => true,
                'minlength' => 3,
                'maxlength' => 255,
                'text' => true,
            ],
            'email' => [
                'required'  => true,
                'email' => true,
                'maxlength' => 255
            ],
            'phone' => [
                'required'  => true,
                'phone' => true,
                'maxlength' => 20
            ],
            'message' => [
                'required'  => true,
                'minlength' => 25,
                'maxlength' => 255,
                'text' => true,
            ]
        ]);

        if ($validation->fails()) {
            return $response->setBody($this->c->view->render('form.twig', [
                'csrf_token' => $this->c->session->token(),
                'old' => $data,
                'errors' => $validation->errors(),
            ]))->withHeader('Content-Type', 'text/html; charset=UTF-8');
        }

        $data = (object) [
            'name' => $data['name'],
            'email' => strtolower($data['email']),
            'phone' => $data['phone'],
            'message' => $data['message'],
            'subscribe' => isset($data['subscribe']) ? true : false,
            'ip' => $request->getServerParams()['REMOTE_ADDR'],
            'user_agent' => $request->getServerParams()['HTTP_USER_AGENT'],
        ];


        try {
            $this->process($data);
        } catch (\Throwable $th) {
            print_r($th->getMessage());
        }

        if (!$this->process($data)) {
            return $response->setBody($this->c->view->render('form.twig', [
                'success' => false,
                'csrf_token' => $this->c->session->token(),
                'old' => $data,
                'message' => 'There was an error sending the email. Please try again'
            ]))->withHeader('Content-Type', 'text/html; charset=UTF-8');
        };

        header("Location: sent");
        exit();
    }

    protected function process($data)
    {

        if (!$lead = $this->fetchLeadByEmail($data->email)) {
            $lead = $this->fetchLeadById($this->insertLead($data));
        }

        $this->updateLead($lead, $data);

        return $this->sendMessage($lead, $data);
    }

    protected function sendMessage($lead, $data)
    {
        $messageId = $this->insertMessage($lead, $data);
        $message = $this->fetchMessageById($messageId);

        $date = $message->createdAtHuman();

        $body = <<<EOD
            Name: $lead->name
            Email: $lead->email
            Message: $message->message
            date: $date
            ip: $message->ip
            User Agent: $message->user_agent
        EOD;

        return mail(
            $this->c->config['admin_email'],
            'New lead request',
            $body,
            "From: $lead->email"
        );
    }

    protected function insertMessage($lead, $data)
    {
        $query = "INSERT INTO messages(lead_id, message, ip, user_agent, updated_at, created_at) VALUES(:lead_id, :message, :ip, :user_agent, :updated_at, :created_at)";
        $now = Carbon::now();

        $statement = $this->db->prepare($query);
        $statement->bindParam(':lead_id', $lead->id, PDO::PARAM_INT);
        $statement->bindParam(':message', $data->message, PDO::PARAM_STR);
        $statement->bindParam(':ip', $data->ip, PDO::PARAM_STR);
        $statement->bindParam(':user_agent', $data->user_agent, PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $now, PDO::PARAM_STR);
        $statement->bindParam(':created_at', $now, PDO::PARAM_STR);
        $statement->setFetchMode(PDO::FETCH_CLASS, Message::class);
        $statement->execute();

        return $this->db->lastInsertId();
    }

    protected function insertLead($data)
    {
        $query = "INSERT INTO leads(email, name, phone, subscribed, ip, user_agent, updated_at, created_at) VALUES(:email, :name, :phone, :subscribed, :ip, :user_agent, :updated_at, :created_at)";
        $now = Carbon::now();

        $statement = $this->db->prepare($query);
        $statement->bindParam(':email', $data->email, PDO::PARAM_STR);
        $statement->bindParam(':name', $data->name, PDO::PARAM_STR);
        $statement->bindParam(':phone', $data->phone, PDO::PARAM_STR);
        $statement->bindParam(':subscribed', $data->subscribe, PDO::PARAM_BOOL);
        $statement->bindParam(':ip', $data->ip, PDO::PARAM_STR);
        $statement->bindParam(':user_agent', $data->user_agent, PDO::PARAM_STR);
        $statement->bindParam(':updated_at', $now, PDO::PARAM_STR);
        $statement->bindParam(':created_at', $now, PDO::PARAM_STR);
        $statement->execute();

        return $this->db->lastInsertId();
    }

    protected function updateLead($lead, $data)
    {
        $query = "UPDATE leads SET name = :name, phone = :phone, subscribed = :subscribed, updated_at = :updated_at WHERE id = :id";
        $now = Carbon::now();

        $statement = $this->db->prepare($query);
        $statement->bindParam(':id', $lead->id, PDO::PARAM_INT);
        $statement->bindParam(':name', $data->name, PDO::PARAM_STR);
        $statement->bindParam(':phone', $data->phone, PDO::PARAM_STR);
        $statement->bindParam(':subscribed', $data->subscribe, PDO::PARAM_BOOL);
        $statement->bindParam(':updated_at', $now, PDO::PARAM_STR);
        $statement->setFetchMode(PDO::FETCH_CLASS, Lead::class);
        return $statement->execute();
    }

    protected function fetchLeadByEmail($email)
    {
        $statement = $this->db->prepare("SELECT * FROM leads WHERE LOWER(email) = :email");
        $statement->bindParam(':email', $email, PDO::PARAM_STR);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, Lead::class);
        return $statement->fetch();
    }

    protected function fetchLeadById($id)
    {
        $statement = $this->db->prepare("SELECT * FROM leads WHERE id = :id");
        $statement->bindParam(':id', strtolower($id), PDO::PARAM_STR);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, Lead::class);
        return $statement->fetch();
    }

    protected function fetchMessageById($id)
    {
        $statement = $this->db->prepare("SELECT * FROM messages WHERE id = :id");
        $statement->bindParam(':id', $id, PDO::PARAM_STR);
        $statement->execute();
        $statement->setFetchMode(PDO::FETCH_CLASS, Lead::class);
        return $statement->fetch();
    }

    public function sent($request, $response)
    {
        return $response->setBody($this->c->view->render('sent.twig'))
            ->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }
}
