<?php
    namespace app\controllers;
    use Codeception\Module\Cli;
use Faker\Provider\cs_CZ\DateTime;
use Yii;
    use yii\filters\AccessControl;
use yii\httpclient\CurlTransport;
use yii\web\Controller;
    use yii\filters\VerbFilter;
    use app\models\LoginForm;
    use app\models\ContactForm;
    use yii\httpclient\Client;

    class SiteController extends Controller {
        private $accessToken = '';
        private $contributorID = '2981518';

        /* other code */
        public function actionSpeak($message = "default message") {
            return $this->render("speak",['message' => $message]);
        }

        public function actionTest(){
            // return a set of rows. each row is an associative array of column names and values.
            // an empty array is returned if the query returned no results
            Yii::$app->db->createCommand()->insert('user', [
                'name' => 'My New User',
                'email' => 'mynewuser@gmail.com',
            ])->execute();
            $user = Yii::$app->db->createCommand('SELECT * FROM user WHERE name = :name')
                ->bindValue(':name', 'My New User')
                ->queryOne();
            var_dump($user);
            // UPDATE (table name, column values, condition)
            Yii::$app->db->createCommand()->update('user', ['name' => 'My New User
         Updated'], 'name = "My New User"')->execute();
            $user = Yii::$app->db->createCommand('SELECT * FROM user WHERE name = :name')
                ->bindValue(':name', 'My New User Updated')
                ->queryOne();
            var_dump($user);
            // DELETE (table name, condition)
            Yii::$app->db->createCommand()->delete('user', 'name = "My New User
         Updated"')->execute();
            $user = Yii::$app->db->createCommand('SELECT * FROM user WHERE name = :name')
                ->bindValue(':name', 'My New User Updated')
                ->queryOne();
            var_dump($user);

        }

        public function actionAuthorize(){
            $client = new Client();
            $redirect_uri = $this->getRedirectURI();
            $redirect_uri .= '/index.php?r=site/client';

            $response = $client->get('https://api.shutterstock.com/v2/oauth/authorize',['client_id' => '1fed276e29a88731787a',
                    'state' => '347yctr4bcfx4unzelmqwjfixz',
                    'scope' => 'user.view',
                    'response_type'=>'code',
                    'redirect_uri'=>$redirect_uri])
                ->setHeaders(['User-Agent'=>'Yii2'])
                ->send();
            $redirect = $response->getHeaders()['Location'];

            $this->redirect($redirect);

        }

        public function actionClient(){
            $client = new Client();

            if (isset($_GET['code'])) {
                $redirect_uri = $this->getRedirectURI();
                $redirect_uri .= '/index.php?r=site/success';

                $response = $client->createRequest()
                    ->setMethod('post')
                    ->setUrl('https://api.shutterstock.com/v2/oauth/access_token')
                    ->setData(['client_id' => '1fed276e29a88731787a', 'client_secret' => '92fb4ce1e90d3747f2deb68134839d86ca88a206',
                        'grant_type' => 'authorization_code', 'code' => $_GET['code'], 'redirect_uri' => $redirect_uri])
                    ->setHeaders(['User-Agent'=>'Yii2'])
                    ->send();

                $this->accessToken = $response->getData()['access_token'];
                //var_dump($this->accessToken);

                $new_redirect_uri = $this->getRedirectURI();
                $new_redirect_uri .= '/index.php?r=site/contributor';
                //var_dump($new_redirect_uri);
                $this->redirect($new_redirect_uri);
            }
        }

        public function actionSuccess(){

            $redirect_uri = $this->getRedirectURI();
            $redirect_uri .= '/index.php?r=site/contributor';
            $this->redirect($redirect_uri);

        }

        private function getRedirectURI(){
            $redirect_uri = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $redirect_uri .= "://{$_SERVER['SERVER_NAME']}";
            if (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 80) {
                $redirect_uri .= ":{$_SERVER['SERVER_PORT']}";
            }
            return $redirect_uri;
        }

        public function actionContributor(){
            $client = new Client();
            //header('Authorization: Bearer v2/MWZlZDI3NmUyOWE4ODczMTc4N2EvMTcyODQ1ODE0L2N1c3RvbWVyLzIvTEc0ZVNHOUNpeFA2M01PRFUyQ0pPVGd5elB0WkJnZV82U1RhMVo2M1kxeW5ZTDRkUmZsUE5PVGJ3WG1NTklKdktiLUVXZXA3b1Z6WUFaN3ZmU2RhQTVfai1FNzhiVHNKNy01cnpwN3FUV1lJMzIwMnhTdlZycDBMVXF2ZmJTWjFqMXM0Q0Y5Rk1fek9KZUJjRkRBUjN4MzNYUFdoNl82TlJ4cWRZbTRaN3FUSExuR01CSDNfSXVCQTUxQUx1XzJz');
            $request = $client->createRequest()
                ->setMethod('get')
                ->setUrl('https://api.shutterstock.com/v2/contributors/'.$this->contributorID)
                ->setHeaders(['User-Agent'=>'Yii2','Authorization' => 'Bearer v2/MWZlZDI3NmUyOWE4ODczMTc4N2EvMTcyODQ1ODE0L2N1c3RvbWVyLzIvTEc0ZVNHOUNpeFA2M01PRFUyQ0pPVGd5elB0WkJnZV82U1RhMVo2M1kxeW5ZTDRkUmZsUE5PVGJ3WG1NTklKdktiLUVXZXA3b1Z6WUFaN3ZmU2RhQTVfai1FNzhiVHNKNy01cnpwN3FUV1lJMzIwMnhTdlZycDBMVXF2ZmJTWjFqMXM0Q0Y5Rk1fek9KZUJjRkRBUjN4MzNYUFdoNl82TlJ4cWRZbTRaN3FUSExuR01CSDNfSXVCQTUxQUx1XzJz']);

            var_dump($request->getHeaders());
            $response = $request->send();
            var_dump($response->getContent());
        }

        public function actionInfo(){
            var_dump($_SERVER);
        }

}
?>
