<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Models\Tva;
use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelReader;


class TvaController extends Controller
{
    private $path = 'tva/' ;
    private $mainFilename = 'tva';
    public function getAllTvaWithCountrie()
    {
        try {
            return response()->json([
                'message' => 'All TVA',
                'data' => Tva::all()
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'an error occured'
            ],500);
        }
    }

    public function updateTva(Tva $tva , Request $request)
    {
        try {
            if (!isset($tva)) {
                return response()->json([
                    'message' => 'TVA not found'
                ],404);
            }

            if (!isset($request->countrie) || !isset($request->tva)) {
                return response()->json([
                    'message' => 'countrie and tva required'
                ],400);
            }
            $tva->update(['countrie' => $request->countrie , 'TVA' => $request->tva]);
            return response()->json([
                'message' => 'TVA updated',
                'data' => $tva
            ],201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'an error occured',
            ],500);
        }
       
    }

    public function importCsvTva(Request $request)
    {
        try {
            if ($request->hasFile('file')) {
                // creation tva dans la base de donnee
                $file = $request->file('file');
                $ext= $file->getClientOriginalExtension();
                if ($ext != "csv") {
                    return response()->json([
                        'message' => 'invalid data format'
                    ],400);
                }

                $fichier = $file->move($this->path, $this->mainFilename.".".$ext);
                $reader = SimpleExcelReader::create($fichier);
                $reader->getRows()->each(function(array $rowProperties) {
                    $a = array_map('trim', array_keys($rowProperties));
                    $b = array_map('trim', $rowProperties);
                    $rowProperties = array_combine($a, $b);
                    Tva::firstOrCreate($rowProperties);
                });
                unlink($this->path.$this->mainFilename.".".$ext);
                return response()->json([
                    'message' => 'File imported',
                    'data' => Tva::all()
                ],201);
            }else{
                return response()->json([
                    'message' => 'File required'
                ],400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'an error occured'
            ],500);
        }
    }

    public function deleteTva(Tva $tva)
    {
        try {
            if (!isset($tva)) {
                return response()->json([
                    'message' => 'TVA not found'
                ],404);
            }
            $tva->delete();
            return response()->json([
                'message' => 'TVA deleted'
            ],200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'an error occured'
            ],500);
        }
        
    }
}
