# Core\EntityEventManager

## Жизненный цикл

1. EntityEventManager через EventHandler'ы формируют кэш. В нем регистрируются DataGetter'ы.
2. CachedDataRepository позволяет обратиться к кэшу.
3. Какой кэш загружать, описывается в DataGetter'е. Там же предуусмотрена политика на случай 
отсутсвия кэша (fallback strategy)