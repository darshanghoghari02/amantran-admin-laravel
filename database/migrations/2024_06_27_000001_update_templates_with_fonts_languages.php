<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update existing templates to include fonts and languages fields
     */
    public function up(): void
    {
        $templates = DB::table('templates')->get();
        
        foreach ($templates as $template) {
            $data = json_decode($template->data, true) ?? [];
            
            // Add fonts field if missing or empty
            if (!isset($data['fonts']) || empty($data['fonts'])) {
                $data['fonts'] = ['KAP011', 'Farsan', 'Hind Vadodara', 'Rasa'];
            }
            
            // Add languages field if missing or empty
            if (!isset($data['languages']) || empty($data['languages'])) {
                $data['languages'] = ['Bengali', 'English', 'Gujarati', 'Hindi', 'Marathi', 'Tamil', 'Urdu'];
            }
            
            // Add singlePurchasePrice if missing
            if (!isset($data['singlePurchasePrice'])) {
                $data['singlePurchasePrice'] = 49;
            }
            
            // Add plan inclusion fields if missing
            if (!isset($data['includedInMonthlyPlan'])) {
                $data['includedInMonthlyPlan'] = false;
            }
            
            if (!isset($data['includedInYearlyPlan'])) {
                $data['includedInYearlyPlan'] = false;
            }
            
            DB::table('templates')->where('id', $template->id)->update([
                'data' => json_encode($data, JSON_UNESCAPED_UNICODE)
            ]);
        }
    }

    /**
     * Reverse the migration
     */
    public function down(): void
    {
        // This migration only adds default values, so we don't need to remove them
        // The fields will remain in the database
    }
};
