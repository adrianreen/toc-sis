<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Models\ModuleInstance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class GenerateAsyncModuleInstances extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'modules:generate-async-instances 
                            {--days-ahead=90 : Number of days ahead to generate instances}
                            {--dry-run : Show what would be created without actually creating}';

    /**
     * The console command description.
     */
    protected $description = 'Generate future module instances based on async cadence settings for standalone modules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysAhead = (int) $this->option('days-ahead');
        $dryRun = $this->option('dry-run');
        $targetDate = now()->addDays($daysAhead);

        $this->info("🚀 Generating async module instances up to: {$targetDate->format('Y-m-d')}");

        if ($dryRun) {
            $this->warn('🔍 DRY RUN MODE - No instances will be created');
        }

        // Get all modules that allow standalone enrolment
        $standaloneModules = Module::where('allows_standalone_enrolment', true)
            ->with('moduleInstances')
            ->get();

        $this->info("Found {$standaloneModules->count()} standalone modules");

        $createdCount = 0;
        $skippedCount = 0;

        foreach ($standaloneModules as $module) {
            $this->line("\n📚 Processing: {$module->module_code} - {$module->title}");
            $this->line("   Cadence: {$module->async_instance_cadence}");

            // Get the latest instance for this module
            $latestInstance = $module->moduleInstances()
                ->where('delivery_style', 'async')
                ->orderBy('start_date', 'desc')
                ->first();

            if (! $latestInstance) {
                $this->warn('   ⚠️  No async instances found - skipping (create initial instance manually)');
                $skippedCount++;

                continue;
            }

            // Generate instances until we reach the target date
            $currentDate = $latestInstance->start_date;
            $instancesForModule = 0;

            while (true) {
                $nextDate = $this->calculateNextStartDate($currentDate, $module->async_instance_cadence);

                // Stop if we've reached our target date
                if ($nextDate->gt($targetDate)) {
                    break;
                }

                // Check if instance already exists for this date
                $existingInstance = $module->moduleInstances()
                    ->where('start_date', $nextDate->format('Y-m-d'))
                    ->first();

                if ($existingInstance) {
                    $this->line("   ✅ Instance already exists for {$nextDate->format('Y-m-d')}");
                    $currentDate = $nextDate;

                    continue;
                }

                // Create the instance
                if (! $dryRun) {
                    $newInstance = ModuleInstance::create([
                        'module_id' => $module->id,
                        'tutor_id' => $latestInstance->tutor_id, // Copy tutor from latest
                        'start_date' => $nextDate,
                        'target_end_date' => $this->calculateEndDate($nextDate, $module->async_instance_cadence),
                        'delivery_style' => 'async',
                    ]);

                    $this->info("   ✨ Created instance: {$nextDate->format('Y-m-d')} to {$newInstance->target_end_date->format('Y-m-d')}");
                } else {
                    $endDate = $this->calculateEndDate($nextDate, $module->async_instance_cadence);
                    $this->info("   🔍 Would create: {$nextDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
                }

                $createdCount++;
                $instancesForModule++;
                $currentDate = $nextDate;
            }

            if ($instancesForModule === 0) {
                $this->line('   ✅ No new instances needed');
            } else {
                $this->line("   📈 {$instancesForModule} instances ".($dryRun ? 'would be created' : 'created'));
            }
        }

        $this->newLine();
        $this->info('🎉 Summary:');
        $this->info("   Total modules processed: {$standaloneModules->count()}");
        $this->info('   Instances '.($dryRun ? 'that would be created' : 'created').": {$createdCount}");
        $this->info("   Modules skipped: {$skippedCount}");

        if ($dryRun) {
            $this->warn("\n🔍 This was a dry run. Use without --dry-run to actually create instances.");
        } else {
            Log::info("Generated {$createdCount} async module instances", [
                'target_date' => $targetDate->format('Y-m-d'),
                'modules_processed' => $standaloneModules->count(),
            ]);
        }

        return Command::SUCCESS;
    }

    /**
     * Calculate the next start date based on cadence
     */
    private function calculateNextStartDate($currentStartDate, $cadence)
    {
        $date = Carbon::parse($currentStartDate);

        switch ($cadence) {
            case 'monthly':
                return $date->addMonth();
            case 'quarterly':
                return $date->addMonths(3);
            case 'bi_annually':
                return $date->addMonths(6);
            case 'annually':
                return $date->addYear();
            default:
                return $date->addMonths(3); // Default to quarterly
        }
    }

    /**
     * Calculate the end date based on start date and cadence
     */
    private function calculateEndDate($startDate, $cadence)
    {
        $date = Carbon::parse($startDate);

        switch ($cadence) {
            case 'monthly':
                return $date->addMonth()->subDay();
            case 'quarterly':
                return $date->addMonths(3)->subDay();
            case 'bi_annually':
                return $date->addMonths(6)->subDay();
            case 'annually':
                return $date->addYear()->subDay();
            default:
                return $date->addMonths(3)->subDay();
        }
    }
}
