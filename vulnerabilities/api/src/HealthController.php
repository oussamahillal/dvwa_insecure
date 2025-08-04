<?php

# Start the app with:
#
# php -S localhost:8000 -t public

namespace Src;

use OpenApi\Attributes as OAT;

class HealthController
{
	private $command = null;
	private $requestMethod = "GET";
	public const SUCCESS = 'Successful operation.';
	public const ana = 'HTTP/1.1 200 OK';
	public const anae = 'HTTP/1.1 500 Internal Server Error';

	public function __construct($requestMethod ,$command) {
		$this->requestMethod = $requestMethod;
		$this->command = $command;
	}

    #[OAT\Post(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/echo',
        operationId: 'echo',
		description: 'Echo, echo, cho, cho, o o ....',
        parameters: [
                new OAT\RequestBody (
					description: 'Your words.',
                    content: new OAT\MediaType(
                        mediaType: 'application/json',
                        schema: new OAT\Schema(ref: Words::class)
                    )
                ),

        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: ApiResponses::SUCCESS,
            ),
        ]
    )
    ]
	
	private function echo() {
		$input = (array) json_decode(file_get_contents('php://input'), true);
		if (array_key_exists ("words", $input)) {
			$words = $input['words'];

			$response['status_code_header'] = ApiResponses::ana;
			$response['body'] = json_encode (array ("reply" => $words));
		} else {
			$response['status_code_header'] = ApiResponses::anae;
			$response['body'] = json_encode (array ("status" => "Words not specified"));
		}
		return $response;
	}

    #[OAT\Post(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/connectivity',
        operationId: 'checkConnectivity',
		description: 'The server occasionally loses connectivity to other systems and so this can be used to check connectivity status.',
        parameters: [
                new OAT\RequestBody (
					description: 'Remote host.',
                    content: new OAT\MediaType(
                        mediaType: 'application/json',
                        schema: new OAT\Schema(ref: Target::class)
                    )
                ),

        ],
        responses: [
            new OAT\Response(
                response: 200,
                description: ApiResponses::SUCCESS,
            ),
        ]
    )
    ]
	

     private function checkConnectivity()
      {
        $input = (array) json_decode(file_get_contents('php://input'), true);
        $response = [];

        if (array_key_exists("target", $input)) {
            $target = $input['target'];

             // 🧪 1. Valider que c’est une adresse IP ou un nom d’hôte valide
            if (filter_var($target, FILTER_VALIDATE_IP) || preg_match('/^([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}$/', $target)) {
            
                // ✅ 2. Échapper correctement l'argument utilisateur
                $sanitized_target = escapeshellarg($target);

                // 🛡️ 3. Exécuter la commande de manière sûre
                exec("ping -c 4 " . $sanitized_target, $output, $ret_var);

                if ($ret_var === 0) {
                     $response['status_code_header'] = ApiResponses::SUCCESS;
                     $response['body'] = json_encode(['status' => 'OK']);
                } else {
                    $response['status_code_header'] = ApiResponses::ERROR;
                    $response['body'] = json_encode(['status' => 'Connection failed']);
            }

            } else {
                $response['status_code_header'] = ApiResponses::ERROR;
                $response['body'] = json_encode(['status' => 'Invalid target format']);
            }

        } else {
            $response['status_code_header'] = ApiResponses::ERROR;
            $response['body'] = json_encode(['status' => 'Target not specified']);
        }

        return $response;
    }

    #[OAT\Get(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/status',
        operationId: 'getHealthStatus',
		description: 'Get the health of the system.',
        responses: [
            new OAT\Response(
                response: 200,
                description: ApiResponses::SUCCESS,
            ),
        ]
    )
    ]
	
	private function getStatus() {
		$response['status_code_header'] = ApiResponses::ana;
		$response['body'] = json_encode (array ("status" => "OK"));
		return $response;
	}

    #[OAT\Get(
		tags: ["health"],
        path: '/vulnerabilities/api/v2/health/ping',
        operationId: 'ping',
		description: 'Simple ping/pong to check connectivity.',
        responses: [
            new OAT\Response(
                response: 200,
                description: ApiResponses::SUCCESS,
            ),
        ]
    )
    ]
	private function ping() {
		$response['status_code_header'] = ApiResponses::ana;
		$response['body'] = json_encode (array ("Ping" => "Pong"));
		return $response;
	}

	public function processRequest() {
		switch ($this->requestMethod) {
			case 'POST':
				switch ($this->command) {
					case "echo":
						$response = $this->echo();
						break;
					case "connectivity":
						$response = $this->checkConnectivity();
						break;
					default:
						$gc = new GenericController("notFound");
						$gc->processRequest();
						exit();
				};
				break;
			case 'GET':
				switch ($this->command) {
					case "status":
						$response = $this->getStatus();
						break;
					case "ping":
						$response = $this->ping();
						break;
					default:
						$gc = new GenericController("notFound");
						$gc->processRequest();
						exit();
				}
				break;
			case 'OPTIONS':
				$gc = new GenericController("options");
				$gc->processRequest();
				break;
			default:
				$gc = new GenericController("notSupported");
				$gc->processRequest();
				break;
		}
		header($response['status_code_header']);
		if ($response['body']) {
			echo $response['body'];
		}
	}
}

#[OAT\Schema(required: ['target'])]
final class Target {
    #[OAT\Property(example: "digi.ninja")]
    public string $target;
}

#[OAT\Schema(required: ['words'])]
final class Words {
    #[OAT\Property(example: "Hello World")]
    public string $words;
}

