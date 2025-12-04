<?php
/**
 * Command Pattern Controller
 * 
 * Handles undo/redo operations for service requests
 */

namespace FixItMati\Controllers;

use FixItMati\Core\Request;
use FixItMati\Core\Response;
use FixItMati\DesignPatterns\Behavioral\Command\CommandInvoker;
use FixItMati\DesignPatterns\Behavioral\Command\UpdateRequestStatusCommand;
use FixItMati\DesignPatterns\Behavioral\Command\AssignTechnicianCommand;

class CommandController
{
    private CommandInvoker $invoker;
    
    public function __construct()
    {
        $this->invoker = new CommandInvoker();
    }
    
    /**
     * Execute a command
     */
    public function execute(Request $request): Response
    {
        $commandType = $request->param('type');
        $requestId = $request->param('request_id');
        $userId = $_SESSION['user_id'] ?? null;
        
        if (!$userId) {
            return Response::json([
                'success' => false,
                'message' => 'User not authenticated'
            ], 401);
        }
        
        try {
            $command = null;
            
            switch ($commandType) {
                case 'update_status':
                    $newStatus = $request->param('status');
                    $notes = $request->param('notes');
                    $command = new UpdateRequestStatusCommand(
                        $requestId,
                        $newStatus,
                        $userId,
                        $notes
                    );
                    break;
                    
                case 'assign_technician':
                    $technicianId = $request->param('technician_id');
                    $command = new AssignTechnicianCommand(
                        $requestId,
                        $technicianId,
                        $userId
                    );
                    break;
                    
                default:
                    return Response::json([
                        'success' => false,
                        'message' => 'Invalid command type'
                    ], 400);
            }
            
            $result = $this->invoker->execute($command);
            
            return Response::json([
                'success' => true,
                'message' => 'Command executed successfully',
                'data' => $result,
                'can_undo' => $this->invoker->canUndo(),
                'can_redo' => $this->invoker->canRedo()
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to execute command: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Undo last command
     */
    public function undo(Request $request): Response
    {
        try {
            if (!$this->invoker->canUndo()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Nothing to undo'
                ], 400);
            }
            
            $result = $this->invoker->undo();
            
            return Response::json([
                'success' => true,
                'message' => 'Command undone successfully',
                'data' => $result,
                'can_undo' => $this->invoker->canUndo(),
                'can_redo' => $this->invoker->canRedo()
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to undo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Redo last undone command
     */
    public function redo(Request $request): Response
    {
        try {
            if (!$this->invoker->canRedo()) {
                return Response::json([
                    'success' => false,
                    'message' => 'Nothing to redo'
                ], 400);
            }
            
            $result = $this->invoker->redo();
            
            return Response::json([
                'success' => true,
                'message' => 'Command redone successfully',
                'data' => $result,
                'can_undo' => $this->invoker->canUndo(),
                'can_redo' => $this->invoker->canRedo()
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to redo: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get command history
     */
    public function history(Request $request): Response
    {
        try {
            $history = $this->invoker->getHistory();
            
            return Response::json([
                'success' => true,
                'data' => [
                    'commands' => $history,
                    'can_undo' => $this->invoker->canUndo(),
                    'can_redo' => $this->invoker->canRedo()
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'message' => 'Failed to get history: ' . $e->getMessage()
            ], 500);
        }
    }
}
