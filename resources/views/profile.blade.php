@extends('main')
@section('content')
<div class="row">
    <div class="col-sm-4 col-lg-3">
        <div class="card">
            <div class="card-header"><b>Connected User</b></div>
            <div class="card-body" id="user_list">
                @foreach($users as $user)
                    <p id="connected_user">{{$user->name}}</p>
                @endforeach
            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-6">
{{--        <div class="card">--}}
{{--            <div class="card-header">--}}
{{--                <div class="row">--}}
{{--                    <div class="col col-md-6" id="chat_header"><b>Chat Area</b>--}}
{{--                        <div class="container">--}}
{{--                            <div class="row justify-content-center">--}}
{{--                                <div class="col-md-8">--}}
{{--                                    <div class="card">--}}
{{--                                        <div class="card-header">Chat Room <span id="total_client" class="float-right"></span></div>--}}
{{--                                        <div class="card-body">--}}
{{--                                            <div id="chat_output" class="pre-scrollable" style="height: 600px">--}}

{{--                                            @foreach($messages as $message)--}}
{{--                                                @if($message->user_id == auth()->id())--}}
{{--                                                        <span class="text-success"><b>{{$message->user_id}}. {{$message->name}}--}}
{{--                                            :</b> {{$message->message}} <span--}}
{{--                                                                class="text-warning float-right">{{date('Y-m-d h:i a', strtotime($message->created_at))}}</span></span>--}}
{{--                                                        <br><br>--}}
{{--                                                    @else--}}
{{--                                                        <span class="text-info"><b>{{$message->user_id}}. {{$message->name}}--}}
{{--                                            :</b> {{$message->message}} <span--}}
{{--                                                                class="text-warning float-right">{{date('Y-m-d h:i a', strtotime($message->created_at))}}</span></span>--}}
{{--                                                        <br><br>--}}
{{--                                                    @endif--}}
{{--                                                @endforeach--}}
{{--                                            </div>--}}
{{--                                            <input id="chat_input" class="form-control" placeholder="Write Message and Press Enter"/>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}


{{--                    </div>--}}
{{--                    <div class="col col-md-6" id="close_chat_area"></div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="card-body" id="chat_area">--}}

{{--            </div>--}}
{{--        </div>--}}
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col col-md-6" id="chat_header"><b>Chat Area</b></div>
                    <div class="col col-md-6" id="close_chat_area"></div>
                </div>
            </div>
            <div class="card-body" id="chat_area">

            </div>
        </div>
    </div>
    <div class="col-sm-4 col-lg-3">
        <div class="card" style="height:255px; overflow-y: scroll;">
            <div class="card-header">
                 <input type="text" name="search" id="search" placeholder="Enter search name" class="form-control" onfocus="this.value=''">
                 <div id="search_list"></div>
            </div>



    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>


    <script>
        $(document).ready(function(){
            $('#search').on('keyup',function(){
                let query= $(this).val();
                $.ajax({
                    url:"search",
                    type:"GET",
                    data:{'search':query},
                    success:function(data){
                        $('#search_list').html(data);
                    }
                });

            });
        });

    </script>

        <script>
        let conn = new WebSocket('ws://127.0.0.1:8090/?token={{ auth()->user()->token }}');

        let from_user_id = "{{ Auth::user()->id }}";


        let to_user_id = "";
        conn.onopen = function(e) {
            console.log('connection successfully');
        }
        conn.onmessage = function (e) {

        }


    </script>





























{{--@endsection('content')--}}

{{--    <script src="{{asset('dist/jquery.js')}}"></script>--}}
{{--    <script type="text/javascript">--}}

{{--        $('document').ready(function () {--}}
{{--            $("#chat_output").animate({scrollTop: $('#chat_output').prop("scrollHeight")}, 1000); // Scroll the chat output div--}}
{{--        });--}}
{{--        // Websocket--}}
{{--        let ws = new WebSocket('ws://127.0.0.1:8090/?token={{ auth()->user()->token }}');--}}
{{--        ws.onopen = function (e) {--}}

{{--            console.log('Connected to websocket');--}}
{{--            ws.send(--}}
{{--                JSON.stringify({--}}
{{--                    'type': 'socket',--}}
{{--                    'user_id': '{{auth()->id()}}'--}}
{{--                })--}}
{{--            );--}}
{{--            // Bind onkeyup event after connection--}}
{{--            $('#chat_input').on('keyup', function (e) {--}}
{{--                if (e.keyCode === 13 && !e.shiftKey) {--}}
{{--                    let chat_msg = $(this).val();--}}
{{--                    ws.send(--}}
{{--                        JSON.stringify({--}}
{{--                            'type': 'chat',--}}
{{--                            'user_id': '{{auth()->id()}}',--}}
{{--                            'user_name': '{{auth()->user()->name}}',--}}
{{--                            'chat_msg': chat_msg--}}
{{--                        })--}}
{{--                    );--}}
{{--                    $(this).val('');--}}
{{--                    console.log('{{auth()->id()}} sent ' + chat_msg);--}}
{{--                }--}}
{{--            });--}}
{{--        };--}}
{{--        ws.onerror = function (e) {--}}
{{--            // Error handling--}}
{{--            console.log(e);--}}
{{--            alert('Check if WebSocket server is running!');--}}
{{--        };--}}
{{--        ws.onclose = function(e) {--}}
{{--            console.log(e);--}}
{{--            alert('Check if WebSocket server is running!');--}}
{{--        };--}}
{{--        ws.onmessage = function (e) {--}}
{{--            console.trace(e);--}}
{{--            let json = JSON.parse(e.data);--}}
{{--            switch (json.type) {--}}
{{--                case 'chat':--}}
{{--                    $('#chat_output').append(json.msg); // Append the new message received--}}
{{--                    $("#chat_output").animate({scrollTop: $('#chat_output').prop("scrollHeight")}, 1000); // Scroll the chat output div--}}
{{--                    console.log("Received " + json.msg);--}}
{{--                    break;--}}
{{--                case 'socket':--}}
{{--                    $('#total_client').html(json.msg);--}}
{{--                    console.log("Received " + json.msg);--}}
{{--                    break;--}}
{{--            }--}}
{{--        };--}}
{{--    </script>--}}
@endsection
