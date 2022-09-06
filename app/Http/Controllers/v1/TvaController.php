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
    private $newTVA = [];

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

    public function updateTva($tva , Request $request)
    {
        $tva = Tva::find($tva);
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

            if ($tva->countrie !=  $request->countrie) {
                $isExiste = Tva::where('countrie',$request->countrie)->first();
                if (isset($isExiste)) {
                    return response()->json([
                        'message' => 'Countrie already existed',
                        'data' => []
                    ],201);
                }else{
                    $tva->update(['countrie' => $request->countrie , 'TVA' => $request->tva]);
                }
            }else{
                $tva->update(['countrie' => $request->countrie , 'TVA' => $request->tva]);
            }

            $this->createNewCsv();
            return response()->json([
                'message' => 'TVA updated',
                'data' => $tva
            ],201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'an error occured',
                'error' => $e->getMessage()
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

                // get file
                $file = $file->move($this->path, $this->mainFilename.".".'csv');
                
                // read file
                $reader = SimpleExcelReader::create($file);
                $reader->getRows()->each(function(array $rowProperties) {
                    $a = array_map('trim', array_keys($rowProperties));
                    $b = array_map('trim', $rowProperties);
                    $existe = Tva::where('countrie',$b['countrie'])->first();
                    if (!isset($existe)) {
                        $rowProperties = array_combine($a, $b);
                        //variable globale
                        $this->newTVA[] = Tva::create($rowProperties);
                    }
                });

                //update download
                $this->createNewCsv();

                return response()->json([
                    'message' => 'File imported',
                    'data' =>  $this->newTVA
                ],201);
            }else{
                return response()->json([
                    'message' => 'File required'
                ],400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'an error occured',
                'error' => $th->getMessage()
            ],500);
        }
    }

    public function createNewCsv()
    {
        $file = fopen(public_path().'/tva/tva.csv', 'w');
        $columns = array('countrie', 'TVA');
        $newTva = Tva::get(['countrie','TVA'])->toArray();
        fputcsv($file, $columns);
        foreach ($newTva as $tva) {
            fputcsv($file, $tva);
        }
        fclose($file);
    }

    public function downloadTva() 
    {
        $file= public_path(). "/tva/tva.csv";
        $headers = array(
            'Content-Type: application/csv',
        );
        return response()->download($file, 'tva.csv', $headers);
    }

    public function deleteTva($tva)
    {
        $tva = Tva::find($tva);
        try {
            if (!isset($tva)) {
                return response()->json([
                    'message' => 'TVA not found'
                ],404);
            }
            $tva->delete();
            $this->createNewCsv();
            return response()->json([
                'message' => 'TVA deleted'
            ],200);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'an error occured',
                'error' => $e->getMessage()
            ],500);
        }
        
    }
}
