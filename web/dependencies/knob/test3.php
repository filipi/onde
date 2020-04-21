<!DOCTYPE html>
<html>
    <head>
        <title>jQuery Knob demo</title>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js"></script>
        <script src="js/jquery.knob-1.0.1.1.js"></script>
        <link href='http://fonts.googleapis.com/css?family=Raleway:400,200' rel='stylesheet' type='text/css'>

        <link href="stylesheet.css" rel="stylesheet" type="text/css">
        <script>
            $(function() {
                $(".knob").knob();
                /*$(".knob").knob(
                                {
                                'change':function(e){
                                        console.log(e);
                                    }
                                }
                            )
                           .val(79)
                           ;*/
            });
        </script>
    </head>
    <body>

        </div>
        <div style="float:left;width:320px;height:300px;padding:20px">
            <input class="knob" data-cursor=true data-skin="tron" value="35">
        </div>

        <div style="float:left;width:320px;height:300px;padding:20px">
            <input class="knob"data-width="250" data-min="-100" value="44">
        </div>

        <div style="float:left;width:320px;height:300px;padding:20px">
            <input class="knob" data-width="300" data-cursor=true value="29">
        </div>

        <script type="text/javascript">
            var _gaq = _gaq || [];
            _gaq.push(['_setAccount', 'UA-3008949-6']);
            _gaq.push(['_trackPageview']);
        </script>
        <script type="text/javascript">
            (function() {
                    var ga = document.createElement('script');
                    ga.type = 'text/javascript';
                    ga.async = true;
                    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
            })();
        </script>
    </body>
</html>
