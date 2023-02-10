<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use App\Models\User;
use App\Models\Chat;
use App\Models\Chat_request;
use Auth;
class SocketController extends Controller implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        $querystring = $conn->httpRequest->getUri()->getQuery();
        parse_str($querystring, $queryarray);

        if (isset($queryarray['token'])) {
            User::where('token', $queryarray['token'])->update(['connection_id' => $conn->resourceId, 'user_status' => 'Online']);

            $user_id = User::select('id')->where('token', $queryarray['token'])->get();

            $data['id'] = $user_id[0]->id;

            $data['status'] = 'Online';

            foreach ($this->clients as $client) {
                $client->send(json_encode($data));
            }
        }
    }
    public function onMessage(ConnectionInterface $conn, $msg)
    {
        $data = json_decode($msg);
        if (isset($data->type)) {
            if ($data->type == 'request_user') {
                $user_data = User::select('id', 'name', 'user_status', 'user_image')
                    ->where('id', '!=', $data->from_user_id)
                    ->get();
                $sub_data = array();
                foreach ($user_data as $row) {
                    $sub_data[] = array(
                        'name' => $row['name'],
                        'id' => $row['id'],
                        'status' => $row['user_status'],
                        'user_image' => $row['user_image']
                    );
                }

                $sender_connection_id = User::select('connection_id')->where('id', $data->from_user_id)->get();

                $send_data['data'] = $sub_data; //users information -name - Ani, email - ...

                $send_data['response_load_unconnected_user'] = true;
                foreach ($this->clients as $client) {
                    if($client->resourceId == $sender_connection_id[0]->connection_id)
                    {
                        $client->send(json_encode($send_data));
                    }
                }
            }
            if ($data->type == 'request_search_user')
            {
                $user_data = User::select('id','name','user_status','user_image')
                    ->where('id','!=',$data->from_user_id)
                    ->where('name','like','%'.$data->search_query.'%')
                    ->orderBy('name','ASC')
                    ->get();
                $sub_data = array();
                foreach ($user_data as $row)
                {
                    $sub_data[] = array(
                        'name' => $row['name'],
                        'id'=>$row['id'],
                        'status'=>$row['user_status'],
                        'user_image'=>$row['user_image']
                    );
                }
                //get login user connection id
                $sender_connection_id = User::select('connection_id')->where('id',$data->from_user_id)->get();
                $send_data['data'] =$sub_data;
                $send_data['response_search_user'] = true;
                //send data to login user
                foreach ($this->clients as $client)
                {
                    if ($client ->resourceId == $sender_connection_id[0]->connection_id)
                    {
                        $client->send(json_encode($send_data));
                    }
                }

            }
            if ($data->type == 'request_send_message') {
                $chat = new Chat;
                $chat->from_user_id = $data->from_user_id;
                $chat->to_user_id = $data->to_user_id;
                $chat->chat_message = $data->message;
                $chat->message_status = 'Not Send';
                $chat->save();
                $chat_message_id = $chat->id;
                $receiver_connection_id = User::select('connection_id')->where('id', $data->to_user_id)->get();
                $sender_connection_id = User::select('connection_id')->where('id', $data->from_user_id)->get();
                foreach ($this->clients as $client) {
                    if ($client->resourceId == $receiver_connection_id[0]->connection_id || $client->resourceId == $sender_connection_id[0]->connection_id) {
                        $send_data['chat_message_id'] = $chat_message_id;
                        $send_data['message'] = $data->message;
                        $send_data['from_user_id'] = $data->from_user_id;
                        $send_data['to_user_id'] = $data->to_user_id;
                        if($client->resourceId == $receiver_connection_id[0]->connection_id)
                        {
                            Chat::where('id', $chat_message_id)->update(['message_status' =>'Send']);
                            $send_data['message_status'] = 'Send';
                        }
                        else
                        {
                            $send_data['message_status'] = 'Not Send';
                        }

                        $client->send(json_encode($send_data));
                    }
                }
            }
            if ($data->type == 'request_chat_history') {
                $chat_data = Chat::select('id', 'from_user_id', 'to_user_id', 'chat_message', 'message_status')
                    ->where(function ($query) use ($data) {
                        $query->where('from_user_id', $data->from_user_id)->where('to_user_id', $data->to_user_id);
                    })
                    ->orWhere(function ($query) use ($data) {
                        $query->where('from_user_id', $data->to_user_id)->where('to_user_id', $data->from_user_id);
                    })->orderBy('id', 'ASC')->get();


                $send_data['chat_history'] = $chat_data;

                $receiver_connection_id = User::select('connection_id')->where('id', $data->from_user_id)->get();

                foreach ($this->clients as $client) {
                    if($client->resourceId == $receiver_connection_id[0]->connection_id)
                    {
                        $client->send(json_encode($send_data));
                    }
                }

            }
            if ($data->type == 'update_chat_status') {
                Chat::where('id', $data->chat_message_id)->update(['message_status' => $data->chat_message_status]);

                $sender_connection_id = User::select('connection_id')->where('id', $data->from_user_id)->get();

                foreach ($this->clients as $client) {
                    if ($client->resourceId == $sender_connection_id[0]->connection_id) {
                        $send_data['update_message_status'] = $data->chat_message_status;

                        $send_data['chat_message_id'] = $data->chat_message_id;

                        $client->send(json_encode($send_data));
                    }
                }
            }
            if($data->type == 'check_unread_message')
            {
                $chat_data = Chat::select('id', 'from_user_id', 'to_user_id')->where('message_status', '!=', 'Read')->where('from_user_id', $data->to_user_id)->get();
                $sender_connection_id = User::select('connection_id')->where('id', $data->from_user_id)->get();
                $receiver_connection_id = User::select('connection_id')->where('id', $data->to_user_id)->get();

                foreach($chat_data as $row)
                {
                    Chat::where('id', $row->id)->update(['message_status' => 'Send']);
                    foreach($this->clients as $client)
                    {
                        if($client->resourceId == $sender_connection_id[0]->connection_id)
                        {
                            $send_data['count_unread_message'] = 1;
                            $send_data['chat_message_id'] = $row->id;
                            $send_data['from_user_id'] = $row->from_user_id;
                        }

                        if($client->resourceId == $receiver_connection_id[0]->connection_id)
                        {
                            $send_data['update_message_status'] = 'Send';
                            $send_data['chat_message_id'] = $row->id;
                            $send_data['unread_message'] = 1;
                            $send_data['from_user_id'] = $row->from_user_id;
                        }
                        $client->send(json_encode($send_data));
                    }
                }
            }
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        $querystring = $conn->httpRequest->getUri()->getQuery();

        parse_str($querystring, $queryarray);

        if(isset($queryarray['token']))
        {
            User::where('token', $queryarray['token'])->update([ 'connection_id' => 0, 'user_status' => 'Offline' ]);

            $user_id = User::select('id', 'updated_at')->where('token', $queryarray['token'])->get();

            $data['id'] = $user_id[0]->id;

            $data['status'] = 'Offline';

            $updated_at = $user_id[0]->updated_at;



            foreach($this->clients as $client)
            {
                if($client->resourceId != $conn->resourceId)
                {
                    $client->send(json_encode($data));
                }
            }
        }
    }
    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()} \n";

        $conn->close();
    }
}
