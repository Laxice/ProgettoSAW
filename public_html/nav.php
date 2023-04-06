<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<link rel="stylesheet" href="style.css">
<script src="https://code.jquery.com/jquery-3.6.3.min.js" integrity="sha256-pvPw+upLPUjgMXY0G+8O0xUf+/Im1MZjXxxgOcBQBXU=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" integrity="sha256-lSjKY0/srUM9BE3dPm+c4fBo1dky2v27Gdjm2uoZaL0=" crossorigin="anonymous"></script>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="icon" type="image/png" href="images/favicon.png"/>

<?php session_start();

function showNav(){
?>
<nav class="navbar navbar-expand-md bg-light">
    <div class="container-fluid d-flex">
        <a class="navbar-brand" href="homepage.php"><img src="images/heart.png" alt="Cuore" width="40" height="40"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse row" id="navbarSupportedContent">
                <?php if(empty($_SESSION["nome"])){?>
                <ul class="navbar-nav justify-content-end col-md-3 order-1 order-md-3">
                    <li class="nav-item my-auto me-3 order-md-4">
                        <a href="login.php"><img src=<?php echo "images/icons/user.png"?> alt="Immagine profilo" class="rounded-circle" width="30" height="30"></a>
                    </li>
                    <li class="nav-item order-md-1">
                        <a class="nav-link" href="login.php">Login</a>
                    </li>
                    <li class="nav-item order-md-2">
                        <a class="nav-link" href="registration.php">Registration</a>
                    </li>
                </ul>
                <?php } else{?>
                <ul class="navbar-nav justify-content-end col-md-3 order-1 order-md-3">
                    <li class="nav-item order-md-4">
                        <div class="dropdown">
                        <button class="btn btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Toggle profile menu">
                            <img src=<?php echo "images/icons/".$_SESSION["immagine_profilo"].".png"?> alt="Immagine profilo" class="rounded-circle" width="30" height="30">
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownProfile">
                            <li><a class="dropdown-item" href="show_profile.php"><?php echo $_SESSION["username"];?></a></li>
                            <li><a class="dropdown-item" href="collection.php">Collezione</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                        </ul>
                        </div>
                    </li>
                    <li class="nav-item order-md-2 my-auto">
                        <span class="nav-link disabled" id="monete"><?php echo $_SESSION["monete"]."$";?></span>
                    </li>
                </ul>
                <?php }?>
            <ul class="navbar-nav col-md-4 order-2 order-md-1">
                <li class="nav-item">
                    <a class="nav-link" href="missions.php">Missioni</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gatcha.php">Gatcha</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="leaderboard.php">Leaderboard</a>
                </li>
            </ul>
            <ul class="navbar-nav col-md-5 order-3 order-md-2">
                <li class="nav-item w-100">
                    <!--<form class="d-flex ui-widget" id="search-box" role="search" >-->
                        <input class="form-control" id="search-input" type="text" placeholder="Ricerca" aria-label="Search">
                        <!--<button class="btn btn-outline-success" id="search-button" type="submit">Search</button>-->
                    <!--</form>-->
                </li>
            </ul>
        </div>
    </div>
</nav>
<script>
    $(document).ready(function(){

        $.widget( "custom.catcomplete", $.ui.autocomplete, {
        _create: function() {
            this._super();
            this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
        },
        _renderMenu: function( ul, items ) {
            var that = this,
                currentCategory = "",
                pos=2;
            $.each( items, function( index, item ) {
                var li;
                if ( item.categoria != currentCategory ) {
                    ul.append( "<li class='ui-autocomplete-category'>" + capitalizeFirstLetter(item.categoria) + "</li>" );
                    currentCategory = item.categoria;
                }
                //li = that._renderItemData( ul, item );
                ul.append( "<li class='ui-menu-item'>" +
                "<div id='ui-id-"+pos+++"' tabindex='-1' class='ui-menu-item-wrapper'><a href='search_result.php?id="+item.id+"&categoria="+item.categoria+"'>"+
                    capitalizeFirstLetter(item.nome )
                 + "<a/></div></li>" );
            });
        }
        });

        $("#search-input").catcomplete({
            source: function (request, response) {
            $.get("search.php?q="+$("#search-input").val(), function (data) {
               var res = JSON.parse(data);
                if(res.error!=null){
                    console.log(res.error);
                }else{
                    console.log(res.search_results);
                    response(res.search_results);
                }
            });
            },
            minLength: 2
        });
    });

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
</script>
<?php
}
?>