<?php


namespace App\Helpers;
use Illuminate\Support\Facades\Log;

class GitHubClass
{
    public $author;
    public $repository;
    private $url;
    private static $compareParams = [
        'Number of forks' => 'Best result by forks',
        'Number of stars' => 'Best result by stars',
        'Number of watchers' => 'Best result by watchers',
        'Date of the latest release' => 'Most new',
        'Pull requests open' => 'Haw many PR is open',
        'Pull requests close' => 'Haw many PR is closed',
    ];

    const FULL_SEARCH = true;
    const REPOS = 'repos/';
    const PULL_REQUEST = 'pulls';
    const RELEASE = 'releases';
    const REPOSITORY_INFO = 'search/repositories?q=';


    /**
     * Static method which compere 2 differnt repositories.
     */
    public static  function compereRepositories($first, $second){
        $first = self::parse($first);
        $second = self::parse($second);

        $secondRepoName = '';
        $firstRepoName = '';
        $ratings = [
            'Number of forks' => '',
            'Number of stars' => '',
            'Number of watchers' => '',
            'Date of the latest release' => '',
            'Pull requests open' => '',
            'Pull requests close' => '',
        ];

        foreach ($second as $name => $v) {
            $secondRepoName = $name;
            break;
        }
        foreach ($first as $name => $v) {
            $firstRepoName = $name;
            break;
        }
        foreach ($ratings as $rate => $val) {
            $ratings[$rate] = self::compareByOneRate($first[$firstRepoName][$rate], $firstRepoName, $second[$secondRepoName][$rate], $secondRepoName);
        }

        $compareResult = [];
        foreach (self::$compareParams as $key => $param) {
            $compareResult[$param] = $ratings[$key];
        }

        return $compareResult;
    }

    private static function compareByOneRate($firstRepo, $firstRepoName, $secondRepo, $secondRepoName) {
        $result = '';
        $resultValue = '';
        if ($firstRepo === 'Date is undefined' || $secondRepo === 'Date is undefined') {
            if (strtotime($firstRepo) === FALSE || strtotime($secondRepo) === FALSE) {
                $result =  'Undefined';
            } else {
                if(strtotime($firstRepo) > strtotime($secondRepo)){
                    $result =  $firstRepoName;
                    $resultValue = $firstRepo;
                } elseif (strtotime($firstRepo) === strtotime($secondRepo)) {
                    $result =  'The value is same';
                } else {
                    $result =  $secondRepoName;
                    $resultValue = $secondRepo;
                }
            }
        } else {
            if($firstRepo > $secondRepo){
                $result =  $firstRepoName;
                $resultValue = $firstRepo;
            } elseif ($firstRepo === $secondRepo) {
                $result =  'The value is same';
            } else {
                $result =  $secondRepoName;
                $resultValue = $secondRepo;
            }
        }
        if($result === 'The value is same' || $result === 'Undefined') {
            return $result;
        }
        return $result.= ' - '.$resultValue;

    }

    private static function parse($repository){
        $repoData = [];
        foreach ($repository as $repoName => $repo) {
            foreach ($repo as $key => $value) {
                if (!is_array($value)) {
                    $repoData[$repoName][$key] = $value;
                } else {
                    foreach ($value as $k => $v) {
                        $repoData[$repoName][$key .' '.$k] = $v;
                    }
                }
            }
        }
        return $repoData;
    }

    public function __construct($author, $repository)
    {
        $this->url = 'https://api.github.com/';
        $this->author = $author;
        $this->repository = $repository;
    }

    /**
     * Method for call to github Api.
     * @param $param
     * @param bool $fullSearch
     * @return array
     */
    private function call($param, $fullSearch = false)
    {

        if ($fullSearch) {
            $requestUrl = $this->url . $param;
        } else {
            $requestUrl = $this->url . self::REPOS . $this->author . '/' . $this->repository . '/' . $param;
        }
        $curlSession = curl_init();
        if ($curlSession === false) {
            throw new Exception('failed to initialize');
        }
        curl_setopt($curlSession, CURLOPT_USERAGENT, 'Awesome-Octocat-App');
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlSession, CURLOPT_URL, $requestUrl);
        curl_setopt($curlSession, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, true);
        $jsonData = json_decode(curl_exec($curlSession));
        $httpcode = curl_getinfo($curlSession, CURLINFO_HTTP_CODE);
        if ($httpcode == 200) {
            $answear = [
                'status' => 'ok',
                'data' => $jsonData
            ];
            return $answear;
        } else {
            Log::info('HTTP ERROR.  code: ' . $httpcode);
        }

    }

    /**
     * Method for getting information about pull requests.
     * @return array
     */
    private function getPullRequests()
    {
        $pullRequests = $this->call(self::PULL_REQUEST);
        $openNumber = 0;
        $closeNumber = 0;
        if (!empty($pullRequests['data'])) {
            foreach ($pullRequests['data'] as $key => $value) {
                if ($value->state === 'open') {
                    $openNumber++;
                } else {
                    $closeNumber++;
                }
            }
        }
        $result = [
            'open' => $openNumber,
            'close' => $closeNumber
        ];
        return $result;
    }

    /**
     * Method for getting information last release.
     * @return false|string
     */
    private function getLatestRelease()
    {
        $release = $this->call(self::RELEASE);
        if (isset($release['data']['0']->published_at)) {
            return date('Y-m-d H:i', strtotime($release['data']['0']->published_at));
        } else {
            return 'Date is undefined';
        }

    }

    /**
     * Method for getting information repository.
     * @return false|string
     */
    public function getRepositoryInfo()
    {
        $request = self::REPOSITORY_INFO . $this->author . '/' . $this->repository;
        $info = $this->call($request, self::FULL_SEARCH);

        if ($info['status'] == 'ok') {
            if ($info['data']->total_count === 0) {
                return $info['data']->total_count;
            }
            $forksCount = $info['data']->items[0]->forks_count;
            $stargazersCount = $info['data']->items[0]->stargazers_count;
            $watchersCount = $info['data']->items[0]->watchers_count;
            $latestRelease = $this->getLatestRelease();
            $pullRequests = $this->getPullRequests();
            $answer = [
                $this->author . '/' . $this->repository => [
                    'Number of forks' => $forksCount,
                    'Number of stars' => $stargazersCount,
                    'Number of watchers' => $watchersCount,
                    'Date of the latest release' => $latestRelease,
                    'Pull requests' => $pullRequests
                ]
            ];
            return $answer;
        } else {
            return 'Repository is not found.';
        }
    }

}
