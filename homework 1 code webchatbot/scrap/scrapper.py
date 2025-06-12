from crawl4ai import AsyncWebCrawler, CacheMode, CrawlerRunConfig

class WebScrapper:
    def __init__(self):
        # Конструктор пока пустой, можно добавить настройки, если нужно
        pass
    
    async def crawl(self, url):
        # Создаём конфигурацию краулера — отключаем кэш, чтобы всегда получать свежие данные
        crawler_config = CrawlerRunConfig(cache_mode = CacheMode.BYPASS)

        # Асинхронно запускаем краулер в контекстном менеджере
        async with AsyncWebCrawler() as crawler:
            # Запускаем обход страницы по заданному URL с указанной конфигурацией
            result = await crawler.arun(url = url, config = crawler_config)

            # Возвращаем извлечённый контент в markdown-формате
            return result.markdown
