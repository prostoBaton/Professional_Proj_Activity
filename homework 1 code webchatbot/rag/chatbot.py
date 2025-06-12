from langchain_ollama import ChatOllama
from langchain_core.prompts import PromptTemplate
from langchain.chains import RetrievalQA
import time

class ChatBot:
    def __init__(self, vector_db, chosen_model):
        self.db = vector_db  # база с векторными представлениями для поиска по контексту
        
        # Выбираем модель LLM в зависимости от выбранного варианта
        match chosen_model:
            case "Deepseek":
                self.llm = ChatOllama(
                    model = "deepseek-r1:1.5b",
                    base_url = "http://localhost:11434",
                    temperature = 0.3
                )
            case "Qwen":
                self.llm = ChatOllama(
                    model = "qwen3:0.6b",
                    base_url = "http://localhost:11434",
                    temperature = 0.3
                )
            case "gemma3":
                self.llm = ChatOllama(
                    model = "gemma3",
                    base_url = "http://localhost:11434",
                    temperature = 0.3
                )
            case "Llama":
                self.llm = ChatOllama(
                    model = "llama3.2:3b",
                    base_url = "http://localhost:11434",
                    temperature = 0.3
                )
        
        self.starttime = 0
        self.endtime = 0
        
        # Шаблон запроса, в котором задаётся формат общения и контекст для модели
        self.prompt_template = """
            You are an AI narrator for a Lovecraftian story.
            The story takes place in 1913 on the board of the steamship
            SS Atlantika, that is traveling across the Atlantic Ocean
            en route to Boston, Massachusetts.
            Your goal is to generate a scenario for the given promt,
            similar to the scenarios in the context. The scenario
            must not exceed 3 sentences or 230 characters.

            context: {context}
            question: {question}

            <response> Your answer in Markdown format. </response>
        """

        # Создаём цепочку с retrieval + LLM, которая будет искать информацию в векторной базе
        self.chain = self.build_chain()
    
    def build_chain(self):
        # Формируем PromptTemplate с переменными context и question
        prompt = PromptTemplate(template=self.prompt_template, input_variables=["context", "question"])

        # Получаем поисковый интерфейс к базе с настройкой на возвращение 5 ближайших результатов
        retriever = self.db.as_retriever(search_kwargs={"k": 5})

        # Строим цепочку RetrievalQA, которая сначала ищет контекст, затем генерирует ответ
        chain = RetrievalQA.from_chain_type(
            llm=self.llm,
            chain_type="stuff",  # тип объединения данных
            retriever=retriever,
            return_source_documents=True,  # возвращать документы с ответом (для отладки)
            chain_type_kwargs={"prompt": prompt},
            verbose=True
        )

        return chain

    def qa(self, question):
        self.starttime = time.time()  # замеряем время начала
        response = self.chain(question)  # запускаем цепочку на вопрос
        return response["result"]  # возвращаем ответ модели
        self.endtime = time.time()  # (этот код не выполняется, т.к. после return)
