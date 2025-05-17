<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <!-- Tarjeta 1: Emprendedores -->
            <div class="relative overflow-hidden rounded-xl border border-border bg-card text-card-foreground p-6">
                <div class="flex flex-col h-full">
                    <h3 class="text-sm font-medium text-muted-foreground">Total Emprendedores</h3>
                    <p class="mt-2 text-3xl font-bold">{{ App\Models\User::where('role_id', '!=', 1)->where('estado', '!=', 'eliminado')->count() }}</p>
                    <div class="mt-4 flex items-center text-sm text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        +12.5%
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">Registrados este mes</p>
                </div>
            </div>
            
            <!-- Tarjeta 2: Eventos -->
            <div class="relative overflow-ahidden rounded-xl border border-border bg-card text-card-foreground p-6">
                <div class="flex flex-col h-full">
                    <h3 class="text-sm font-medium text-muted-foreground">Total Eventos</h3>
                    <p class="mt-2 text-3xl font-bold">{{ App\Models\Event::where('estado', '!=', 'eliminado')->count() }}</p>
                    <div class="mt-4 flex items-center text-sm text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        +8.2%
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">Activos actualmente</p>
                </div>
            </div>
            
            <!-- Tarjeta 3: Stands -->
            <div class="relative overflow-hidden rounded-xl border border-border bg-card text-card-foreground p-6">
                <div class="flex flex-col h-full">
                    <h3 class="text-sm font-medium text-muted-foreground">Total Stands</h3>
                    <p class="mt-2 text-3xl font-bold">{{ App\Models\Stand::where('estado', '!=', 'eliminado')->count() }}</p>
                    <div class="mt-4 flex items-center text-sm text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                        </svg>
                        +5.7%
                    </div>
                    <p class="mt-2 text-xs text-muted-foreground">Disponibles: {{ App\Models\Stand::where('estado', 'disponible')->count() }}</p>
                </div>
            </div>
        </div>
        
        <!-- Área principal -->
        <div class="relative h-full flex-1 overflow-hidden rounded-xl border border-border bg-card text-card-foreground">
            <div class="relative z-10 p-6">
                <h2 class="text-lg font-semibold">Actividad Reciente</h2>
                <!-- Aquí puedes agregar gráficos o tablas de actividad reciente -->
                <div class="mt-4 text-sm text-muted-foreground">
                    Próximamente: Gráficos de actividad y estadísticas
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>