<?php
// src/includes/api/BaseApi.php

class BaseApi
{
    protected $db;
    protected $requestMethod;
    protected $id;

    public function __construct($db, $requestMethod, $id = null)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->id = $id;
    }

    protected function getJSONBody()
    {
        return json_decode(file_get_contents("php://input"), true);
    }

    protected function sendResponse($data, $statusCode = 200)
    {
        header("Content-Type: application/json");
        http_response_code($statusCode);
        echo json_encode($data);
    }

    protected function sendError($message, $statusCode = 400)
    {
        $this->sendResponse(["error" => $message], $statusCode);
    }

    protected function validateRequired($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case "GET":
                if ($this->id) {
                    $response = $this->getOne($this->id);
                } else {
                    $response = $this->getAll();
                }
                break;
            case "POST":
                $response = $this->create();
                break;
            case "PUT":
                if (!$this->id) {
                    throw new Exception("ID is required for update");
                }
                $response = $this->update($this->id);
                break;
            case "DELETE":
                if (!$this->id) {
                    throw new Exception("ID is required for deletion");
                }
                $response = $this->delete($this->id);
                break;
            default:
                throw new Exception("Invalid request method");
        }
        return $response;
    }

    // Abstract methods to be implemented by child classes
    protected function getAll()
    {
        throw new Exception("Method not implemented");
    }

    protected function getOne($id)
    {
        throw new Exception("Method not implemented");
    }

    protected function create()
    {
        throw new Exception("Method not implemented");
    }

    protected function update($id)
    {
        throw new Exception("Method not implemented");
    }

    protected function delete($id)
    {
        throw new Exception("Method not implemented");
    }
}
