@extends('main')
@section('content')
    <div class="row">
        <div class="col-sm-4 col-lg-3">
            <div class="card">
                <div class="card-header"><b>
                        <input type="text" placeholder="Search User ..." onkeyup="search_user('{{Auth::id()}}',this.value);">
                    </b></div>
                <div id="search_list"></div>
                <div class="card-body" id="user_list">
                    {{-- add list of users--}}

                </div>
            </div>
        </div>
        <div class="col-sm-4 col-lg-6" style="width: 900px">
            <div class="card" >
                <div class="card-header">
                    <div class="row" id="chat">
                        <div class="col col-md-6" id="chat_header"><b>Chat</b></div>

                    </div>
                </div>
                <div class="card-body" id="chat_area">

                </div>
            </div>
        </div>
    </div>

    <style>


        #chat_area
        {
            min-height: 500px;
        }

        #chat_history
        {
            min-height: 500px;
            max-height: 500px;
            overflow-y: scroll;
            margin-bottom:16px;
            background-color: #ece5dd;
            padding: 16px;
        }

        #user_list
        {
            min-height: 500px;
            max-height: 500px;
            overflow-y: scroll;
        }
    </style>

@endsection('content')

{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>--}}
{{--<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>--}}
{{--<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>--}}
{{--<script>--}}
{{--    $(document).ready(function(){--}}
{{--        $('#search').on('keyup',function(){--}}
{{--            let query= $(this).val();--}}
{{--            $.ajax({--}}
{{--                url:"search",--}}
{{--                type:"GET",--}}
{{--                data:{'search':query},--}}
{{--                success:function(data){--}}
{{--                    $('#search_list').html(data);--}}
{{--                }--}}
{{--            });--}}

{{--        });--}}
{{--    });--}}

{{--</script>--}}
<script>

    let conn = new WebSocket('ws://127.0.0.1:8090/?token={{ auth()->user()->token }}');
    let from_user_id = "{{ Auth::user()->id }}";
    let to_user_id = "";

    conn.onopen = function(e){
        console.log("Connection established!");
        load_unconnected_user(from_user_id);
    };
    conn.onmessage = function(e){
        let data = JSON.parse(e.data);

        if(data.response_load_unconnected_user || data.response_search_user)
        {
            let  html = '';
            if(data.data.length > 0)
            {
                for(let count = 0; count < data.data.length; count++)
                {
                    html += `<a href="#" onclick="make_chat_area(`+data.data[count].id+`, '`+data.data[count].name+`'); load_chat_data(`+from_user_id+`, `+data.data[count].id+`) "> <br>`;
                    if(data.data[count].status == 'Online')
                    {
                        html += '<span class="text-success online_status_icon" id="status_'+data.data[count].id+'"><i class="fas fa-circle"></i></span>';
                    }
                    else
                    {
                        html += '<span class="text-danger online_status_icon" id="status_'+data.data[count].id+'"><i class="fas fa-circle"></i></span> ';
                    }
                    let user_image = `<img src="{{ asset('images/no-image.png') }}" width="35" height="35"  />`;
                    html += `&nbsp; `+user_image+`&nbsp;<b>`+data.data[count].name+`</b>
                    <span class="user_unread_message" data-id="`+data.data[count].id+`" id="user_unread_message`+data.data[count].id+`"></span>`;

                }
            }
            else
            {
                html += 'No User Found';
            }
            document.getElementById('user_list').innerHTML = html;

            check_unread_message();
        }
        if(data.message)
        {
            let html = '';

            if(data.from_user_id == from_user_id)
            {
                let icon_style = '';
                if(data.message_status == 'Not Send')
                {
                    icon_style = '<span id="chat_status_'+data.chat_message_id+'" class="float-end"><i class="fas fa-check-double text-muted"></i></span>';
                }
                if(data.message_status == 'Send')
                {
                    icon_style = '<span id="chat_status_'+data.chat_message_id+'" class="float-end"><i class="fas fa-check-double text-muted"></i></span>';
                }

                if(data.message_status == 'Read')
                {
                    icon_style = '<span class="text-primary float-end" id="chat_status_'+data.chat_message_id+'"><i class="fas fa-check-double"></i></span>';
                }
                html += `<div class="row">
				            <div class="col col-3">&nbsp;</div>
				            <div class="col col-9 alert alert-success text-dark shadow-sm">`+data.message+ icon_style +`</div>
			            </div>`;
            } else {

                if (to_user_id != '') //user online
                {
                    html += data.message;
                    update_message_status(data.chat_message_id,from_user_id,to_user_id,'Read');
                } else {
                    let count_unread_msg = document.getElementById('user_unread_message'+data.from_user_id+'');
                    console.log(count_unread_msg);

                }
            }
            if(html != '')
            {
                let previous_chat_element = document.querySelector('#chat_history');
                let chat_history_element = document.querySelector('#chat_history');
                chat_history_element.innerHTML = previous_chat_element.innerHTML + html;
                scroll_top();
            }
        }
        if(data.chat_history)
        {
            let html = '';
            for(let count = 0; count < data.chat_history.length; count++)
            {
                if(data.chat_history[count].from_user_id == from_user_id) {
                    let icon_style = '';
                    if (data.chat_history[count].message_status == 'Not Send') {
                        icon_style = '<span id="chat_status_' + data.chat_history[count].id + '" class="float-end"><i class="fas fa-check text-muted"></i></span>';
                    }
                    else if (data.chat_history[count].message_status == 'Send') {
                        icon_style = '<span id="chat_status_' + data.chat_history[count].id + '" class="float-end"><i class="fas fa-check-double text-muted"></i></span>';
                    }
                    else if (data.chat_history[count].message_status == 'Read') {
                        icon_style = '<span class="text-primary float-end" id="chat_status_' + data.chat_history[count].id + '"><i class="fas fa-check-double"></i></span>';
                    }
                    html += `<div class="row">
					            <div class="col col-3">&nbsp;</div>
					            <div class="col col-9 alert alert-success text-dark shadow-sm">` + data.chat_history[count].chat_message + icon_style + `</div>
				            </div>`;
                }
                else
                {
                    if(data.chat_history[count].message_status != 'Read')
                    {
                        update_message_status(data.chat_history[count].id, data.chat_history[count].from_user_id, data.chat_history[count].to_user_id, 'Read');
                    }
                    html += `<div class="row">
					            <div class="col col-9 alert alert-light text-dark shadow-sm">`+data.chat_history[count].chat_message+`</div>
				            </div>`;
                }
            }
            document.querySelector('#chat_history').innerHTML = html;
            scroll_top();
        }

        if(data.update_message_status)
        {
            let chat_status_element = document.querySelector('#chat_status_'+data.chat_message_id+'');
            if(chat_status_element)
            {
                if(data.update_message_status == 'Read')
                {
                    chat_status_element.innerHTML = '<i class="fas fa-check-double text-primary"></i>';
                }
                if(data.update_message_status == 'Send')
                {
                    chat_status_element.innerHTML = '<i class="fas fa-check-double text-muted"></i>';
                }
            }
            if(data.unread_message)

            {
                let count_unread_message_element = document.getElementById('user_unread_message'+data.from_user_id+'');

                if(count_unread_message_element)
                {
                    let count_unread_message = count_unread_message_element.textContent;

                    if(count_unread_message == '')
                    {
                        count_unread_message = parseInt(0)+1 ;
                    }
                    else
                    {
                        count_unread_message = parseInt(count_unread_message) + 1;
                    }

                    console.log(count_unread_message);
                    count_unread_message_element.innerHTML = '<span style="color: red">'+count_unread_message+'</span>';
                }
            }

        }
    }

    function scroll_top()
    {
        document.querySelector('#chat_history').scrollTop = document.querySelector('#chat_history').scrollHeight;
    }
    function load_unconnected_user(from_user_id)
    {
        let data = {
            from_user_id : from_user_id,
            type : 'request_user'
        };

        conn.send(JSON.stringify(data));
    }
    function search_user(from_user_id,search_query)
    {
        if (search_query.length > 0)
        {
            let data = {
                from_user_id : from_user_id,
                search_query : search_query,
                type : 'request_search_user'
            }
            conn.send(JSON.stringify(data));
        } else {
            load_unconnected_user(from_user_id);
        }
    }
    function make_chat_area(user_id, to_user_name)
    {
        let html = `<div id="chat_history"></div>
	                <div class="input-group mb-3">
		                <div id="message_area" class="form-control" contenteditable style="min-height:125px; border:1px solid #ccc; border-radius:5px;"></div>
                        <button type="button" class="btn btn-success" id="send_button" onclick="send_chat_message()"><i class="fas fa-paper-plane"></i></button>
                    </div>`;

        document.getElementById('chat_area').innerHTML = html;

        document.getElementById('chat_header').innerHTML = 'Chat with <b>'+to_user_name+'</b>';


        to_user_id = user_id;
    }
    function send_chat_message()
    {
        document.querySelector('#send_button').disabled = true;

        let message = document.getElementById('message_area').innerHTML.trim();

        let data = {
            message : message,
            from_user_id : from_user_id,
            to_user_id : to_user_id,
            type : 'request_send_message'
        };

        conn.send(JSON.stringify(data));

        document.querySelector('#message_area').innerHTML = '';

        document.querySelector('#send_button').disabled = false;
    }
    // chat history
    function load_chat_data(from_user_id, to_user_id)
    {
        let data = {
            from_user_id : from_user_id,
            to_user_id : to_user_id,
            type : 'request_chat_history'
        };

        conn.send(JSON.stringify(data));
    }
    function update_message_status(chat_message_id, from_user_id, to_user_id, chat_message_status)
    {
        let data = {
            chat_message_id : chat_message_id,
            from_user_id : from_user_id,
            to_user_id : to_user_id,
            chat_message_status : chat_message_status,
            type : 'update_chat_status'
        };

        conn.send(JSON.stringify(data));
    }
    function check_unread_message()
    {
        let unread_element = document.getElementsByClassName('user_unread_message');

        for(let count = 0; count < unread_element.length; count++)
        {
            let temp_user_id = unread_element[count].dataset.id;

            let data = {
                from_user_id : from_user_id,
                to_user_id : temp_user_id,
                type : 'check_unread_message'
            };

            conn.send(JSON.stringify(data));
        }
    }


</script>
