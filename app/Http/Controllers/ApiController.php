<?php

namespace hub_statisitc\Http\Controllers;

use App\Helpers\GitHubClass as GitHub;
use Illuminate\Http\Request;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    public function compareRepositories(Request $request){

        $status = 'ok';
        $response = [];
        $message = '';

        $validator = Validator::make($request->all(), [
            'first_repository' => 'required|max:50|min:3|',
            'second_repository' => 'required|max:50|min:3|regex:/\//'
        ]);

        if ($validator->fails()) {
            $response = $validator->messages();
            $message = 'Validation error. Please check your params.';
        } else {
            $firstRepository = explode('/', $request->first_repository);
            $secondRepository = explode('/', $request->second_repository);
            if (count($firstRepository) === 1) {
                $firstRepository[1] = $firstRepository[0];
            }
            if (count($secondRepository) === 1) {
                $secondRepository[1] = $secondRepository[0];
            }

            $repositoryFirst = new GitHub($firstRepository[0], $firstRepository[1]);
            $repositorySecond = new GitHub($secondRepository[0], $secondRepository[1]);

            $repositoryInfoFirst = $repositoryFirst->getRepositoryInfo();
            $repositoryInfoSecond = $repositorySecond->getRepositoryInfo();

            if ($repositoryInfoFirst !== 0 && $repositoryInfoSecond !== 0) {
                $comperedInfo = GitHub::compereRepositories($repositoryInfoFirst, $repositoryInfoSecond);
                $response = [
                    'compered info' => $comperedInfo,
                    'first repository info' => $repositoryInfoFirst,
                    'second repository info' => $repositoryInfoSecond,
                ];
            } else {
                if($repositoryInfoFirst === 0) {
                    $message .= ' By first repository name nothing found.';
                }

                if($repositoryInfoSecond === 0) {
                    $message .= ' By second repository name nothing found.';
                }
            }
        }
        return response()->json([
            'status'   => $status ,
            'message'  => $message,
            'response' => $response
        ],200);
    }
}
