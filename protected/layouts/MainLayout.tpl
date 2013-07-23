<html>
  
    <com:THead>
    <meta charset="utf-8">
    <title>Bootstrap, from Twitter</title>
    <style type="text/css">
      body {
        padding-top: 60px;
        padding-bottom: 40px;
      }
      .sidebar-nav {
        padding: 9px 0;
      }

      @media (max-width: 980px) {
        /* Enable use of floated navbar text */
        .navbar-text.pull-right {
          float: none;
          padding-left: 5px;
          padding-right: 5px;
        }
      }
    </style>
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css">        
    </com:THead>
 
  
  <body>
    <div class="navbar navbar-inverse navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container-fluid">
          <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="brand" href="#">Project name</a>
          <div class="nav-collapse collapse">
            <p class="navbar-text pull-right">Logged in as
              <a href="#" class="navbar-link">Username</a>
            </p>
            <ul class="nav">
              <li class="active">
                <a href="#">Home</a>
              </li>
              <li>
                <a href="">About</a>
              </li>
              <li>
                <a href="">Contact</a>
              </li>
            </ul>
          </div>
          <!--/.nav-collapse -->
        </div>
      </div>
    </div>
    <div class="container-fluid">
      <div class="row-fluid">
        <div class="span3">
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">Herramientas</li>
              <li class="active">
                <a href="#">Link</a>
              </li>
              <li><com:THyperLink Text="Despachos" NavigateUrl="?page=herramientas.Despachos"/></li>
              <li>
                <a href="#">Link</a>
              </li>
              <li>
                <a href="#">Link</a>
              </li>
              <li class="nav-header">Sidebar</li>
              <li>
                <a href="#">Link</a>
              </li>
              <li>
                <a href="#">Link</a>
              </li>
              <li>
                <a href="#">Link</a>
              </li>
              <li>
                <a href="#">Link</a>
              </li>
              <li>
                <a href="#">Link</a>
              </li>
              <li>
                <a href="#">Link</a>
              </li>
              <li class="nav-header">Sidebar</li>
              <li>
                <a href="#">Link</a>
              </li>
              <li>
                <a href="#">Link</a>
              </li>
              <li>
                <a href="#">Link</a>
              </li>
            </ul>
          </div>
          <!--/.well -->
        </div>
          
        <!--/span-->
        <div class="span9">
            <com:TContentPlaceHolder ID="Main"/>
          <!--/row-->
        </div>
        <!--/span-->
      </div>
      <!--/row-->
      <hr>
      <footer>
        <p>&copy; Company 2013</p>
      </footer>
    </div>
    <!--/.fluid-container-->
  </body>

</html>