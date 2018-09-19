<?php
//zend by QQ:123456  商创网络  禁止倒卖 一经发现停止任何服务
namespace App\Console\Commands;

class CustomerService extends \Illuminate\Console\Command
{
	/**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'app:chat {action=start} {--d}';
	/**
     * The console command description.
     *
     * @var string
     */
	protected $description = 'customer service';
	/**
     * Workerman Event Handler
     *
     * @var object
     */
	protected $workermanEvent;

	public function __construct(\App\Extensions\WorkerEvent $workermanEvent)
	{
		parent::__construct();
		$this->workermanEvent = $workermanEvent;
	}

	public function handle()
	{
		$action = $this->argument('action');

		if (!in_array($action, array('start', 'stop', 'restart', 'reload', 'status'))) {
			$this->error('Error Arguments');
			exit();
		}

		global $argv;
		$argv[0] = 'app:chat';
		$argv[1] = $action;
		$argv[2] = $this->option('d') ? '-d' : '';
		$ip = $this->workermanEvent->getListenIp();
		$port_status = $index = 0;
		$chat_config = base_path('config/chat.php');
		$config = require $chat_config;

		do {
			$newPort = isset($config['port']) ? $config['port'] : rand(2048, 65535);
			$port_status = $this->checkPort($ip, $newPort);
			$this->workermanEvent->setPort($newPort);
			$config['port'] = (string) $newPort;
			file_put_contents($chat_config, "<?php\n\r return " . var_export($config, 1) . ';');
			$index++;
		} while ($port_status != 2 && $index < 3);

		if ($this->workermanEvent->is_ssl) {
			$ws_worker = new \Workerman\Worker('websocket://' . $this->workermanEvent->getListenIp() . ':' . $this->workermanEvent->getPort(), $this->workermanEvent->getcontext());
			$ws_worker->transport = 'ssl';
		}
		else {
			$ws_worker = new \Workerman\Worker('websocket://' . $this->workermanEvent->getListenIp() . ':' . $this->workermanEvent->getPort());
		}

		$ws_worker->count = 1;
		$ws_worker->serviceContainer = array();
		$ws_worker->customerContainer = array();
		$ws_worker->eventContainer = $this->workermanEvent;
		if ($action == 'stop' || $action == 'restart') {
			echo "change service status...\n";
			$ws_worker->eventContainer->changeServiceStatus();
			echo "all service in logout status\n";
		}

		$ws_worker->onConnect = function($connection) {
			echo 'new connection from ip ' . $connection->getRemoteIp() . "\n";
		};
		$ws_worker->onMessage = function($connection, $data) use($ws_worker) {
			$data = json_decode($data, 1);
			$data['store_id'] = isset($data['store_id']) ? intval($data['store_id']) : 0;
			$data['goods_id'] = isset($data['goods_id']) ? intval($data['goods_id']) : 0;
			$event = $ws_worker->eventContainer;

			switch ($data['type']) {
			case 'login':
				$connection->uid = $data['uid'];
				$connection->uname = $data['name'];
				$data['user_type'] = isset($data['user_type']) && $data['user_type'] == 'service' ? 'service' : 'customer';
				$connection->userType = $data['user_type'];
				$connection->avatar = $data['avatar'];
				$connection->origin = $data['origin'];

				if ($data['user_type'] == 'service') {
					$connection->store_id = $data['store_id'];
					$ws_worker->serviceContainer[$data['store_id']][$data['uid']] = $connection;
					$event->customerLogin($data['uid'], 1);
				}
				else if ($data['user_type'] == 'customer') {
					if (isset($ws_worker->customerContainer[$data['uid']])) {
						$msg = array('message_type' => 'others_login');
						$event->sendinfo($ws_worker->customerContainer[$data['uid']], $msg);
					}

					$connection->targetService = array('store_id' => $data['store_id']);
					$ws_worker->customerContainer[$data['uid']] = $connection;
				}

				$connection->send(json_encode(array('msg' => 'yes', 'message_type' => 'init')));
				break;

			case 'sendmsg':
				$msg = array('from_id' => $connection->uid, 'name' => $connection->uname, 'time' => date('H:i:s'), 'message' => $data['msg'], 'avatar' => $data['avatar'], 'goods_id' => $data['goods_id'], 'store_id' => $data['store_id'], 'message_type' => 'come_msg', 'origin' => $data['origin']);

				if ($connection->userType == 'customer') {
					if (empty($data['to_id'])) {
						$msg['user_type'] = 'service';
						$msg['origin'] = $data['origin'];
						$msg['status'] = 1;
						$event->savemsg($msg);
						$msg['user_type'] = 'customer';
						$msg['message_type'] = 'come_wait';

						if (isset($ws_worker->serviceContainer[$data['store_id']])) {
							foreach ($ws_worker->serviceContainer[$data['store_id']] as $uid => $con) {
								$event->sendinfo($con, $msg);
							}
						}
					}
					else {
						$msg['to_id'] = $data['to_id'];

						if (isset($ws_worker->serviceContainer[$data['store_id']][$data['to_id']])) {
							$msg['status'] = 0;
							$event->sendmsg($ws_worker->serviceContainer[$data['store_id']][$data['to_id']], $msg);
							$connection->targetService = array('store_id' => $data['store_id'], 'sid' => $data['to_id']);
						}
						else if (!isset($ws_worker->serviceContainer[$data['store_id']][$data['to_id']])) {
							$msg['user_type'] = 'service';
							$msg['status'] = 1;
							$event->savemsg($msg);
						}
					}
				}
				else if ($connection->userType == 'service') {
					if (empty($data['to_id']) || !isset($ws_worker->customerContainer[$data['to_id']])) {
						$msg['to_id'] = $data['to_id'];
						$msg['status'] = 1;
						$event->savemsg($msg);
						break;
					}

					if ($ws_worker->customerContainer[$data['to_id']]->targetService['store_id'] == $data['store_id'] && (!isset($ws_worker->customerContainer[$data['to_id']]->targetService['sid']) || $ws_worker->customerContainer[$data['to_id']]->targetService['sid'] == '')) {
						$ws_worker->customerContainer[$data['to_id']]->targetService = array('store_id' => $data['store_id'], 'sid' => $connection->uid);
						$msg['status'] = 0;
						$event->sendmsg($ws_worker->customerContainer[$data['to_id']], $msg);
					}
					else {
						if ($ws_worker->customerContainer[$data['to_id']]->targetService['store_id'] == $data['store_id'] && $ws_worker->customerContainer[$data['to_id']]->targetService['sid'] == $connection->uid) {
							$msg['status'] = 0;
							$event->sendmsg($ws_worker->customerContainer[$data['to_id']], $msg);
						}
						else if ($ws_worker->customerContainer[$data['to_id']]->origin == 'H5') {
							$msg['to_id'] = $data['to_id'];
							$msg['status'] = 1;
							$event->savemsg($msg);
						}
						else {
							$event->sendmsg($ws_worker->customerContainer[$data['to_id']], $msg);
						}
					}
				}

				break;

			case 'info':
				$msg = array('cus_id' => $data['msg'], 'ser_id' => $data['from_id'], 'message_type' => 'robbed', 'goods_id' => $data['goods_id'], 'store_id' => $data['store_id']);

				if (isset($ws_worker->serviceContainer[$data['store_id']])) {
					foreach ($ws_worker->serviceContainer[$data['store_id']] as $uid => $con) {
						if ($con->uid == $data['from_id']) {
							continue;
						}

						$event->sendinfo($con, $msg);
					}
				}

				$event->changemsginfo($msg);
				$msg = array('service_id' => $data['from_id'], 'name' => $connection->uname, 'store_id' => $data['store_id'], 'message_type' => 'user_robbed');

				if (isset($ws_worker->customerContainer[$data['msg']])) {
					$msg['msg'] = $event->getreply(array('service_id' => $data['from_id']));
					$msg['avatar'] = $ws_worker->serviceContainer[$data['store_id']][$data['from_id']]->avatar;
					$event->sendinfo($ws_worker->customerContainer[$data['msg']], $msg);
					$ws_worker->customerContainer[$data['msg']]->targetService = array('store_id' => $data['store_id'], 'sid' => $data['from_id']);
				}

				break;

			case 'change_service':
				if ($connection->userType == 'service') {
					if (isset($ws_worker->customerContainer[$data['cus_id']]) && isset($ws_worker->serviceContainer[$data['store_id']][$data['from_id']])) {
						$ws_worker->customerContainer[$data['cus_id']]->targetService = array('store_id' => $data['store_id'], 'sid' => $data['to_id']);
						$msg = array('sid' => $data['to_id'], 'fid' => $data['from_id'], 'store_id' => $data['store_id'], 'message_type' => 'change_service');
						$event->sendinfo($ws_worker->customerContainer[$data['cus_id']], $msg);
						$msg = array('sid' => $data['to_id'], 'fid' => $data['from_id'], 'cus_id' => $data['cus_id'], 'message_type' => 'change_service');
						$event->sendinfo($connection, $msg);
						$msg = array('sid' => $data['to_id'], 'fid' => $data['from_id'], 'cus_id' => $data['cus_id'], 'store_id' => $data['store_id'], 'message_type' => 'change_service');
						$event->sendinfo($ws_worker->serviceContainer[$data['store_id']][$data['to_id']], $msg);
					}
				}

				break;

			case 'close_link':
				$msg = array('to_id' => $data['to_id'], 'msg' => '客服已断开', 'message_type' => 'close_link');

				if (isset($ws_worker->customerContainer[$data['to_id']])) {
					$event->sendinfo($ws_worker->customerContainer[$data['to_id']], $msg);
				}

				break;
			}
		};
		$ws_worker->onClose = function($connection) use($ws_worker) {
			$event = $ws_worker->eventContainer;

			if ($connection->userType == 'service') {
				unset($ws_worker->serviceContainer[$connection->store_id][$connection->uid]);
			}
			else if ($connection->userType == 'customer') {
				$msg = array('message_type' => 'others_login');

				if (isset($ws_worker->customerContainer[$connection->uid])) {
					$event->sendinfo($ws_worker->customerContainer[$connection->uid], $msg);
				}

				unset($ws_worker->customerContainer[$connection->uid]);
			}

			$event->customerLogin($connection->uid, 0);

			foreach ($connection->worker->connections as $con) {
				$user = array('uid' => $connection->uid, 'message_type' => 'leave');
				$event->sendmsg($con, $user);
			}
		};
		$ws_worker->onWorkerStop = function() use($ws_worker) {
			echo "change service status...\n";
			$ws_worker->eventContainer->changeServiceStatus();
			echo "all service in logout status\n";
		};
		\Workerman\Worker::runAll();
	}

	private function checkPort($ip, $port)
	{
		$sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		socket_set_nonblock($sock);
		socket_connect($sock, $ip, $port);
		socket_set_block($sock);
		$return = @socket_select($r = array($sock), $w = array($sock), $f = array($sock), 3);
		socket_close($sock);
		return $return;
	}
}

?>
