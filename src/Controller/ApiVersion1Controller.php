<?php

namespace GemeenteAmsterdam\ThumbnailService\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use GemeenteAmsterdam\ThumbnailService\Model\ThumbnailRequest;

/**
 * @Route("/thumbnail/1")
 */
class ApiVersion1Controller extends Controller
{
    /**
     * @Route("/generate")
     */
    public function createAction(Request $request, ValidatorInterface $validator)
    {
        $input = new ThumbnailRequest(
            $request->request->get('url', $request->query->get('url')),
            $request->request->getInt('width', $request->query->getInt('width', 210)),
            $request->request->getInt('height', $request->query->getInt('height', 297)),
            $request->request->get('inputType', $request->query->get('inputType')),
            $request->request->get('outputType', $request->query->get('outputType', 'png'))
        );
        
        $errors = $validator->validate($input);
        if ($errors->count() > 0) {
            return new JsonResponse($errors->__toString(), JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $tmpDirectory = $this->getParameter('kernel.project_dir') . '/var/tmp';
        $jobId = $request->getClientIp() . '-' . time() . '-' . uniqid();
        $jobDirectory = $tmpDirectory . DIRECTORY_SEPARATOR . $jobId;
        mkdir($jobDirectory);
        $inFile = $jobDirectory . DIRECTORY_SEPARATOR . 'input.' . $input->inputType;
        $outFile = $jobDirectory . DIRECTORY_SEPARATOR . 'output.' . $input->outputType;
        $fp = fopen($inFile, 'w');
        
        $ch = curl_init($input->url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        fclose($fp);
        
        if ($statusCode < 200 || $statusCode > 300) {
            return new JsonResponse(['msg' => 'input download failed', 'statusCode' => $statusCode], JsonResponse::HTTP_BAD_REQUEST);
        }
        
        $response = new JsonResponse(['msg' => 'unknown error', JsonResponse::HTTP_BAD_REQUEST]);
        if ($input->inputType === 'pdf') {
            $cmd = ('convert ' . $inFile . '[0] -resize ' . $input->width . 'x' . $input->height . ' ' . $outFile);
            exec($cmd);
            $response = new JsonResponse([
                'jobId' => $jobId,
                'result' => base64_encode(file_get_contents($outFile))
            ]);
        } elseif ($input->inputType === 'docx') {
            $activeDirectory = getcwd();
            chdir($jobDirectory);
            $cmd = ('soffice --headless --convert-to pdf --outdir ' . $jobDirectory . ' ' . $inFile);
            $res = exec($cmd);
            chdir($activeDirectory);
            $tmpFile = str_replace('.docx', '.pdf', $inFile);
            $cmd = ('convert ' . $tmpFile . '[0] -resize ' . $input->width . 'x' . $input->height . ' ' . $outFile);
            exec($cmd);
            $response = new JsonResponse([
                'jobId' => $jobId,
                'result' => base64_encode(file_get_contents($outFile))
            ]);
            unlink($tmpFile);
        }
        
        unlink($inFile);
        unlink($outFile);
        rmdir($tmpDirectory . DIRECTORY_SEPARATOR . $jobId);
        
        return $response;
    }
}
