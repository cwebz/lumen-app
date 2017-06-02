<!doctype html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

        <title>MFL Slack Form</title>

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Raleway', sans-serif;
                font-weight: 100;
                height: 100vh;
                margin: 0;
            }

            .panel {
                margin: 0px auto;
                margin-top: 5%;
                width: 30%;
            }

            .form-section {
                padding-top: 20px;
                padding-bottom: 10px;
            }

            .form-section .slack-name{
                margin-bottom: 10px;
            }
        </style>
    </head>
    <body>
        <div class="panel panel-default">
            <div class="panel-heading">Sign into your MFL account</div>
            <div class="panel-body">
                {{ Form::open( array('action' => 'RegisterFormController@verify') ) }}
                <div class="form-group">
                    {{ Form::open( array('action' => 'RegisterFormController@verify') ) }}
                    <div class="form-section">
                        {!! Form::label('MFL Username or Email *') !!}
                        {!! Form::text('email', null, 
                            array('required', 
                                'class'=>'form-control', 
                                'placeholder'=>'Your e-mail address')) !!}
                    </div>
                    <div class="form-section"> 
                        {!! Form::label('MFL Password *') !!}
                        {!! Form::text('password', null, 
                            array('required', 
                                'class'=>'form-control', 
                                'placeholder'=>'Your password')) !!}
                    </div>
                    <div class="form-section"> 
                        <div class="alert alert-info">
                            <p>Slack username to send alerts to.</p>
                            <p>Multiple names will have a private slack group
                            created.</p>
                        </div>
                        {!! Form::label('Slack Username(s) *') !!}
                        {!! Form::text('username-1', null, 
                            array('required', 
                                'class'=>'form-control slack-name')) !!}

                        {!! Form::text('username-2', null, 
                            array('class'=>'form-control slack-name')) !!}
                        
                        {!! Form::text('username-3', null, 
                            array('class'=>'form-control slack-name')) !!}
                    </div>

                    {!! Form::submit('Register',
                        array('class'=>'btn btn-primary')) !!}

                </div>
                {{ Form::close() }}
            </div>
            </div>
        </div>
    </body>
</html>
