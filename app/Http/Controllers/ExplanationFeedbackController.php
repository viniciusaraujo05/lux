<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BibleExplanation;
use App\Models\ExplanationFeedback;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExplanationFeedbackController extends Controller
{
    /**
     * Store a new feedback for a Bible explanation.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Store a new feedback for a Bible explanation.
     * Resposta imediata, processamento em segundo plano
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Validação inicial - apenas o necessário para verificar se é uma requisição válida
            $validatedData = $request->validate([
                'bible_explanation_id' => 'required|integer',
                'is_positive' => 'required',
                'comment' => 'nullable|string|max:1000',
                'testament' => 'required|string|max:10',
                'book' => 'required|string|max:50',
                'chapter' => 'required|integer',
                'verses' => 'nullable|string|max:100',
            ]);
            
            // Verificação rápida se a explicação existe
            $explanation = BibleExplanation::find($validatedData['bible_explanation_id']);
            
            if (!$explanation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Explicação bíblica não encontrada',
                ], 404);
            }
            
            // Geramos um ID temporário para a resposta
            $tempId = uniqid('feedback_');
            
            // Enviamos a resposta imediatamente para o usuário
            $response = response()->json([
                'success' => true,
                'message' => 'Feedback recebido com sucesso!',
                'temp_id' => $tempId
            ]);
            
            // Essa função faz com que o PHP envie a resposta para o usuário e continue processando
            if (function_exists('fastcgi_finish_request')) {
                $response->send();
                fastcgi_finish_request();
            } else {
                // Para servidores que não suportam fastcgi, enviamos a resposta
                // e fazemos um hack para evitar problemas
                ignore_user_abort(true);
                ob_start();
                $response->send();
                $size = ob_get_length();
                header("Content-Length: $size");
                ob_end_flush();
                flush();
            }
            
            // A partir daqui, o processamento continua no background, e o usuário já recebeu sua resposta
            
            // Tratar o valor booleano
            $rawValue = $validatedData['is_positive'];
            if (is_bool($rawValue)) {
                $isPositive = $rawValue;
            } elseif (is_string($rawValue)) {
                $lowercaseValue = strtolower($rawValue);
                if ($lowercaseValue === 'true' || $lowercaseValue === '1' || $lowercaseValue === 't' || $lowercaseValue === 'yes' || $lowercaseValue === 'y') {
                    $isPositive = true;
                } elseif ($lowercaseValue === 'false' || $lowercaseValue === '0' || $lowercaseValue === 'f' || $lowercaseValue === 'no' || $lowercaseValue === 'n') {
                    $isPositive = false;
                } else {
                    throw new \InvalidArgumentException('O valor de is_positive deve ser um booleano válido');
                }
            } elseif (is_numeric($rawValue)) {
                $isPositive = (int)$rawValue !== 0;
            } else {
                throw new \InvalidArgumentException('O valor de is_positive deve ser um booleano válido');
            }
            
            // Iniciar transação
            DB::beginTransaction();
            
            // Inserir o feedback no banco
            $feedbackId = DB::selectOne(
                'INSERT INTO explanation_feedbacks (bible_explanation_id, is_positive, comment, testament, book, chapter, verses, user_ip, user_agent, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()) RETURNING id',
                [
                    $validatedData['bible_explanation_id'],
                    $isPositive ? 'true' : 'false',
                    $validatedData['comment'] ?? '',
                    $validatedData['testament'],
                    $validatedData['book'],
                    $validatedData['chapter'],
                    $validatedData['verses'] ?? '',
                    $request->ip(),
                    $request->userAgent()
                ]
            )->id;
            
            // Atualizar contadores de feedback
            if ($isPositive) {
                $explanation->positive_feedback_count = ($explanation->positive_feedback_count ?? 0) + 1;
            } else {
                $explanation->negative_feedback_count = ($explanation->negative_feedback_count ?? 0) + 1;
            }
            
            $explanation->save();
            DB::commit();
            
            Log::info('Feedback registrado com sucesso em background', [
                'id' => $feedbackId,
                'temp_id' => $tempId
            ]);
            
            // Não é necessário retornar resposta aqui, pois o usuário já recebeu uma resposta
            // Retornamos uma resposta vazia para satisfazer o tipo de retorno
            return response()->json([], 200);
            
        } catch (\Exception $e) {
            // Apenas fazemos rollback e logamos o erro, usuário já recebeu uma resposta
            DB::rollBack();
            
            Log::error('Erro ao processar feedback em background', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $request->all()
            ]);
            
            // Como estamos em background, retornamos uma resposta vazia
            return response()->json([], 200);
        }
    }

    /**
     * Get feedback statistics for a Bible explanation.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats($id)
    {
        try {
            $explanation = BibleExplanation::find($id);
            
            if (!$explanation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Explicação bíblica não encontrada',
                ], 404);
            }
            
            $positiveCount = $explanation->positive_feedback_count ?? 0;
            $negativeCount = $explanation->negative_feedback_count ?? 0;
            $totalCount = $positiveCount + $negativeCount;
            
            $stats = [
                'positive_count' => $positiveCount,
                'negative_count' => $negativeCount,
                'total_count' => $totalCount,
            ];
            
            if ($totalCount > 0) {
                $stats['positive_percentage'] = round(($positiveCount / $totalCount) * 100);
                $stats['negative_percentage'] = round(($negativeCount / $totalCount) * 100);
            } else {
                $stats['positive_percentage'] = 0;
                $stats['negative_percentage'] = 0;
            }
            
            return response()->json($stats);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar estatísticas de feedback', [
                'error' => $e->getMessage(),
                'id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas de feedback',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
