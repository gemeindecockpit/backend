<?php
use Psr\Container\ContainerInterface;
use Psr\Http\Message\UploadedFileInterface;

require_once('RouteController.php');
/**
* handles all request regarding SVGs
*/
class SVGRouteController extends RouteController {

   // constructor receives container instance
   public function __construct(ContainerInterface $container) {
       parent::__construct($container);
   }


    public function get_org_svg($request, $response, $args) {
      $user_con = new UserController();
      $svg = ''; //encoded in base64

      //can the user see the org? $args['org_id']
      if($user_con->can_see_organisation($_SESSION['user_id'], $args['org_id'])) {
        $SVG_con = new SVGController();
        $SVG_path = $SVG_con->get_SVG_for_org($args['org_id']);
        //does the org have a svg ? if not than send the default svg back
        if ($SVG_path['svg_path'] == '0') {
          $response->getBody()->write(DEFAULT_SVG);
          return $response->withStatus(200,'no svg set');
        } else if (file_exists(SVG_PATH . '/' . $SVG_path['svg_path'])){
          $svg = file_get_contents(SVG_PATH .'/'. $SVG_path['svg_path']);
          $svg = base64_encode($svg);
          $response->getBody()->write($svg);
          return $response->withStatus(200);
        } else {
          return $response->withStatus(403,'no such file');
        }
      } else {
        return $response->withStatus(403,'no access to org or organisation does not exists');
      }

    }
    /**
    * checks if the uploaded file is not a svg or is bigger than the file-limit
    */
    public function set_org_svg($request, $response, $args) {
      $user_con = new UserController();
      if($user_con->can_alter_organisation($_SESSION['user_id'], $args['org_id'])) {
        try {
          $uploadedFiles = $request->getUploadedFiles();
          // handle single input with single file upload
          $uploadedFile = $uploadedFiles['svg_file'];
          if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            //is the filesize bigger than allowed?
            if ($uploadedFile->getSize() > MAX_SVG_SIZE) {
              return $response->withStatus(413,'filesize too big, max size is ' . MAX_SVG_SIZE);
            } else {
              //check if the mimetype is image/svg+xml and if the extension is .svg
              if ($uploadedFile->getClientMediaType() != 'image/svg+xml' && !preg_match("/\.(svg)$/", $uploadedFile->getClientFilename())) {
                return $response->withStatus(406,'file is not a svg')->withHeader('Content-Type','.svg');
              } else {
                //all checks are correct, move file to system and make the DB entry
                $filename = $this->move_uploaded_file(SVG_PATH, $uploadedFile);

                $SVG_con = new SVGController();
                $SVG_con->set_SVG_for_org($args['org_id'], $filename);
              }
            }
          }
        } catch (Exception $e) {
          error_log($e->getMessage());
          return $response->withStatus(500,'does the file-form contain svg_file ?');
        }
      } else {
        return $response->withStatus(403,'no access to org or organisation does not exists');
      }
      return $response->withStatus(200);
    }

    /**
     * Moves the uploaded file to the upload directory and assigns it a unique name
     * to avoid overwriting an existing uploaded file.
     *
     * @param string $directory directory to which the file is moved
     * @param UploadedFile $uploadedFile file uploaded file to move
     * @return string filename of moved file
     */
    function move_uploaded_file(string $directory, UploadedFileInterface $uploadedFile) {
        //$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

        // see http://php.net/manual/en/function.random-bytes.php
        $basename = bin2hex(random_bytes(8)) . $uploadedFile->getClientFilename();
        $filename = sprintf('%s', $basename);

        $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

        return $filename;
    }



}
