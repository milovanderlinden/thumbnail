<?php

namespace GemeenteAmsterdam\ThumbnailService\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HelpController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $serviceUrl = $this->generateUrl('gemeenteamsterdam_thumbnailservice_apiversion1_create');
        $html = <<<EOD
<html>
  <head>
    <title>Thumbnail Service</title>
  </head>
  <body>
    <form method="GET" action="{$serviceUrl}" id="form">
      <p>
        <label>
          URL naar bestand
          <input type="text" name="url">
        </label>
      </p>
      <p>
        <label>
          Thumbnail breedte
          <input type="number" name="width">
        </label>
      </p>
      <p>
        <label>
          Thumbnail hoogte
          <input type="number" name="height">
        </label>
      </p>
      <p>
        <label>
          Invoer bestandstype
          <select name="inputType">
            <option value="pdf">pdf</option>
            <option value="docx">docx</option>
          </select>
        </label>
      </p>
      <p>
        <label>
          Thumbnail bestandstype
          <select name="outputType">
            <option value="png">png</option>
            <option value="jpg">jpg</option>
          </select>
        </label>
      </p>
      <p>
        <button type="submit">JSON</button>
        <button type="submit" id="submit-img">img</button>
      </p>
    </form>
    <div id="result"></div>

    <script type="text/javascript">
window.addEventListener('DOMContentLoaded', function () {
  document.getElementById('submit-img').addEventListener('click', function (event) {
    event.preventDefault();
    document.getElementById('result').innerHTML = 'bezig...';
    var request = new XMLHttpRequest();
    request.open('POST', document.getElementById('form').getAttribute('action'), true);
    request.onload = function() {
      if (request.status >= 200 && request.status < 400) {
        var data = JSON.parse(request.responseText);
        var img = document.createElement('img');
        var outputType = document.getElementsByName('outputType')[0].value;
        img.setAttribute('src', 'data:image/' + outputType + ';base64,' + data.result);
        document.getElementById('result').innerHTML = '';
        document.getElementById('result').appendChild(img);
      } else {
        document.getElementById('result').innerHTML = 'error';
      }
    };
    request.send(new FormData(document.getElementById('form')));
  });
});
    </script>
  </body>
</html>
EOD;
        return new Response($html);
    }
}
