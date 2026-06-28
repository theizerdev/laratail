<?php
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] #[Title('Inicio - Laratail Store')] class extends Component {
    //
};
?>

<div>
    <!-- Hero Slider -->
    <livewire:store.partials.hero-slider />

    <!-- Categories -->
    <livewire:store.partials.categories-grid />

    <!-- Product Catalog -->
    <livewire:store.catalog :show-breadcrumbs="false" />

    <!-- Sale Banner -->
    <livewire:store.partials.sale-banner />

    <!-- New Arrivals -->
    <livewire:store.partials.new-arrivals />

    <!-- Recently Viewed -->
    <livewire:store.partials.recently-viewed-bar />

    <!-- Brands -->
    <livewire:store.partials.brands-showcase />

    <!-- Newsletter -->
    <livewire:store.partials.newsletter-banner />

    <!-- Trust Badges -->
    <livewire:store.partials.trust-badges />
</div>
