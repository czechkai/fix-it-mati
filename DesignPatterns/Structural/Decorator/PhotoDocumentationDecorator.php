<?php
/**
 * Decorator Pattern - Photo Documentation Decorator
 * 
 * Adds photo documentation features
 */

namespace FixItMati\DesignPatterns\Structural\Decorator;

class PhotoDocumentationDecorator extends RequestDecorator
{
    private array $photos = [];
    private float $documentationFee = 0.0; // Free feature
    
    public function __construct(ServiceRequestInterface $request, array $photos = [])
    {
        parent::__construct($request);
        $this->photos = $photos;
    }
    
    /**
     * Get enhanced description
     */
    public function getDescription(): string
    {
        $photoCount = count($this->photos);
        return $this->request->getDescription() . " (📷 {$photoCount} photos attached)";
    }
    
    /**
     * Process with photo documentation
     */
    public function process(): array
    {
        $result = $this->request->process();
        
        // Add photo features
        $result['features'][] = 'photo_documentation';
        $result['photo_count'] = count($this->photos);
        $result['photos'] = $this->photos;
        $result['visual_evidence'] = true;
        
        return $result;
    }
}
