<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Connection;

class DefaultController extends Controller
{
    /**
     * @return Response
     */
    public function index(Request $request)
    {
        $conn = $this->get('doctrine.dbal.default_connection');
        $this->createTable($conn);
        $userIp = $this->getIp($request);
        $this->insert($conn, $userIp);
        $visits = $this->getLast10($conn);

        return new Response(implode("\n", $visits), 200, ['Content-Type' => 'text/plain']);
    }

    private function createTable(Connection $conn)
    {
        $conn->query('CREATE TABLE IF NOT EXISTS visits ' .
        '(time_stamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP, user_ip CHAR(64))');
    }

    private function getIp(Request $request)
    {
        $ip = $request->GetClientIp();
       // Keep only the first two octets of the IP address.
       $octets = explode($separator = ':', $ip);
       if (count($octets) < 2) {  // Must be ip4 address
           $octets = explode($separator = '.', $ip);
       }
       if (count($octets) < 2) {
           $octets = ['bad', 'ip'];  // IP address will be recorded as bad.ip.
       }
       $octets = array_map(function ($x) {
           return $x == '' ? '0' : $x;
       }, $octets);

       return $octets[0] . $separator . $octets[1];
    }

    private function insert(Connection $conn, string $userIp)
    {
        $insert = $conn->prepare('INSERT INTO visits (user_ip) values (:user_ip)');
        $insert->execute(['user_ip' => $userIp]);
    }

    private function getLast10(Connection $conn)
    {
        $select = $conn->prepare(
        'SELECT * FROM visits ORDER BY time_stamp DESC LIMIT 10');
        $select->execute();
        $visits = ["Last 10 visits:"];
        while ($row = $select->fetch(\PDO::FETCH_ASSOC)) {
            array_push($visits, sprintf('Time: %s Addr: %s', $row['time_stamp'],
                $row['user_ip']));
        }
        return $visits;
    }
}
