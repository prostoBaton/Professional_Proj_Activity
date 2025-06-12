from langchain_ollama import ChatOllama
from langchain_core.prompts import PromptTemplate

class WebSummarizer:
    def __init__(self):
        # Инициализируем LLM-модель Ollama с нужными параметрами
        self.llm = ChatOllama(
            model = "deepseek-r1:1.5b",      # название модели
            base_url = "http://localhost:11434",  # локальный сервер модели
            temperature = 0.3,               # степень креативности ответа
            max_tokens = 200                 # максимальная длина ответа
        )

        # Шаблон запроса для создания резюме
        self.prompt_template =  """
                You are an AI assistant that is tasked with summarizing a web page.
                Your summary should be detailed and cover all key points mentioned in the web page.
                Below is the extracted content of the web page:
                {content}

                Please provide a comprehensive and detailed summary in Markdown format.
            """
        
    def summarize(self, content):
        # Формируем запрос, вставляя текст страницы в шаблон
        summary_prompt = PromptTemplate(template = self.prompt_template, input_variables = ["content"])
        prompt_text = summary_prompt.format(content = content)

        # Оборачиваем запрос в формат сообщений для модели
        messages = [{
            "role": "user",
            "content": prompt_text
        }]

        # Отправляем запрос в модель и получаем ответ
        response = self.llm.invoke(messages)

        # Возвращаем полученное резюме
        return response.content
