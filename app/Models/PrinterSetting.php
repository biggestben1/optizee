<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrinterSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'connection_type',
        'ip_address',
        'port',
        'usb_port',
        'paper_width',
        'encoding',
        'auto_cut',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'port' => 'integer',
        'paper_width' => 'integer',
        'auto_cut' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getConnectionString(): string
    {
        if ($this->type === 'network' && $this->ip_address) {
            return $this->ip_address . ':' . $this->port;
        }
        
        if ($this->type === 'usb' && $this->usb_port) {
            return $this->usb_port;
        }
        
        return 'browser';
    }
}
